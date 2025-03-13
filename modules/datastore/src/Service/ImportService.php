<?php

namespace Drupal\datastore\Service;

use CsvParser\Parser\Csv;
use Drupal\common\DataResource;
use Drupal\common\EventDispatcherTrait;
use Drupal\datastore\Events\DatastoreImportedEvent;
use Drupal\datastore\Plugin\QueueWorker\ImportJob;
use Drupal\datastore\Storage\DatabaseTable;
use Drupal\datastore\Storage\DatabaseTableFactory;
use Drupal\datastore\Storage\ImportJobStoreFactory;
use Drupal\metastore\Reference\ReferenceLookup;
use Procrastinator\Result;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Datastore importer.
 *
 * @todo This class has state and is not actually a service because it holds
 *   state. Have import() take an argument of a resource, instead of storing it
 *   as a property.
 */
class ImportService {

  use EventDispatcherTrait;

  /**
   * Event name used when configuring the parser during import.
   *
   * @var string
   */
  public const EVENT_CONFIGURE_PARSER = 'dkan_datastore_import_configure_parser';

  /**
   * Event name for when the datastore has been successfully imported.
   */
  public const EVENT_DATASTORE_IMPORTED = 'dkan_datastore_imported';


  /**
   * Time-limit used for standard import service.
   *
   * @var int
   */
  protected const DEFAULT_TIMELIMIT = 50;

  /**
   * The qualified class name of the importer to use.
   */
  private string $importerClass = ImportJob::class;

  /**
   * The DKAN Resource to import.
   */
  private ?DataResource $resource;

  /**
   * The jobstore factory service.
   */
  private ImportJobStoreFactory $importJobStoreFactory;

  /**
   * Database table factory service.
   */
  private DatabaseTableFactory $databaseTableFactory;

  /**
   * Import job for the current import.
   *
   * Access using self::getImporter().
   *
   * @see self::getImporter()
   */
  private ?ImportJob $importJob = NULL;

  /**
   * Logger channel service.
   */
  private LoggerInterface $logger;

  /**
   * Event dispatcher service.
   */
  private EventDispatcherInterface $eventDispatcher;

  /**
   * Reference lookup service.
   *
   * @var \Drupal\metastore\Reference\ReferenceLookup
   */
  protected $referenceLookup;

  /**
   * Create a resource service instance.
   *
   * @param \Drupal\common\DataResource $resource
   *   DKAN Resource.
   * @param \Drupal\datastore\Storage\ImportJobStoreFactory $importJobStoreFactory
   *   Import jobstore factory.
   * @param \Drupal\datastore\Storage\DatabaseTableFactory $databaseTableFactory
   *   Database Table factory.
   * @param \Psr\Log\LoggerInterface $loggerChannel
   *   DKAN logger channel service.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
   *   Event dispatcher service.
   * @param \Drupal\metastore\Reference\ReferenceLookup $referenceLookup
   *   The reference lookup service.
   */
  public function __construct(
    DataResource $resource,
    ImportJobStoreFactory $importJobStoreFactory,
    DatabaseTableFactory $databaseTableFactory,
    LoggerInterface $loggerChannel,
    EventDispatcherInterface $eventDispatcher,
    ReferenceLookup $referenceLookup,
  ) {
    $this->resource = $resource;
    $this->importJobStoreFactory = $importJobStoreFactory;
    $this->databaseTableFactory = $databaseTableFactory;
    $this->logger = $loggerChannel;
    $this->eventDispatcher = $eventDispatcher;
    $this->referenceLookup = $referenceLookup;
  }

  /**
   * Setter.
   */
  public function setImporterClass($className) {
    $this->importerClass = $className;
  }

  /**
   * Get DKAN resource.
   *
   * @return \Drupal\common\DataResource
   *   DKAN Resource.
   */
  protected function getResource(): DataResource {
    return $this->resource;
  }

  /**
   * Import.
   */
  public function import() {
    $result = $this->getImporter()->run();
    $data_resource = $this->getResource();

    if ($result->getStatus() === Result::ERROR) {
      $this->logger->error('Error importing resource id:%id path:%path message:%message', [
        '%id' => $data_resource->getUniqueIdentifier(),
        '%path' => $data_resource->getFilePath(TRUE),
        '%message' => $result->getError(),
      ]);
    }
    // If the import job finished successfully...
    elseif ($result->getStatus() === Result::DONE) {
      // Dispatch the import event.
      $this->eventDispatcher->dispatch(
        new DatastoreImportedEvent($data_resource),
        self::EVENT_DATASTORE_IMPORTED
      );
      // Queue the imported resource for post-import processing.
      $post_import_queue = \Drupal::service('queue')->get('post_import');
      $post_import_queue->createItem($data_resource);

      // Invalidate cache tag.
      $uid = $data_resource->getIdentifier() . '__' . $data_resource->getVersion();
      $this->invalidateCacheTags($uid . '__source');
    }
  }

  /**
   * Build an Importer.
   *
   * @return \Drupal\datastore\Plugin\QueueWorker\ImportJob
   *   Importer.
   *
   * @throws \Exception
   *   Throws exception if we cannot create a valid importer object.
   */
  public function getImporter(): ImportJob {
    if ($this->importJob ?? FALSE) {
      return $this->importJob;
    }
    $data_resource = $this->getResource();

    $delimiter = ",";
    if ($data_resource->getMimeType() == 'text/tab-separated-values') {
      $delimiter = "\t";
    }

    $this->importJob = call_user_func([$this->importerClass, 'get'],
      md5($data_resource->getUniqueIdentifier()),
      $this->importJobStoreFactory->getInstance(),
      [
        "storage" => $this->getStorage(),
        "parser" => $this->getNonRecordingParser($delimiter),
        "resource" => $data_resource,
      ]
    );

    $this->importJob->setTimeLimit(self::DEFAULT_TIMELIMIT);

    return $this->importJob;
  }

  /**
   * Create a non-recording parser.
   *
   * When processing chunk size was increased to boost performance, the state
   * machine's default behavior to record every execution steps caused out of
   * memory errors. Stopping the machine's recording addresses this.
   *
   * @param string $delimiter
   *   Delimiter character.
   *
   * @return \CsvParser\Parser\Csv
   *   A parser which does not keep track of every execution steps.
   */
  private function getNonRecordingParser(string $delimiter) : Csv {
    $parserConfiguration = [
      'delimiter' => $delimiter,
      'quote' => '"',
      'escape' => "\\",
      'record_end' => ["\n", "\r"],
    ];

    $parserConfiguration = $this->dispatchEvent(self::EVENT_CONFIGURE_PARSER, $parserConfiguration);

    $parser = Csv::getParser($parserConfiguration['delimiter'], $parserConfiguration['quote'], $parserConfiguration['escape'], $parserConfiguration['record_end']);
    $parser->machine->stopRecording();
    return $parser;
  }

  /**
   * Build a database table storage object.
   *
   * @return \Drupal\datastore\Storage\DatabaseTable
   *   DatabaseTable storage object.
   */
  public function getStorage(): DatabaseTable {
    $data_resource = $this->getResource();
    return $this->databaseTableFactory->getInstance($data_resource->getUniqueIdentifier(), ['resource' => $data_resource]);
  }

  /**
   * Invalidate all appropriate cache tags for this resource.
   *
   * @param mixed $resourceId
   *   A resource ID.
   */
  protected function invalidateCacheTags(mixed $resourceId) {
    $this->referenceLookup->invalidateReferencerCacheTags('distribution', $resourceId, 'downloadURL');
  }

}
