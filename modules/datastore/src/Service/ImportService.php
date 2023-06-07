<?php

namespace Drupal\datastore\Service;

use CsvParser\Parser\Csv;
use Drupal\datastore\Plugin\QueueWorker\ImportJob;
use Drupal\common\EventDispatcherTrait;
use Drupal\common\LoggerTrait;
use Drupal\common\DataResource;
use Drupal\common\Storage\JobStoreFactory;
use Drupal\datastore\Storage\DatabaseTable;
use Drupal\datastore\Storage\DatabaseTableFactory;
use Procrastinator\Result;

/**
 * Datastore import service.
 */
class ImportService {
  use LoggerTrait;
  use EventDispatcherTrait;

  /**
   * Event name used when configuring the parser during import.
   *
   * @var string
   */
  public const EVENT_CONFIGURE_PARSER = 'dkan_datastore_import_configure_parser';

  /**
   * Time-limit used for standard import service.
   *
   * @var int
   */
  protected const DEFAULT_TIMELIMIT = 50;

  /**
   * The qualified class name of the importer to use.
   *
   * @var \Procrastinator\Job\AbstractPersistentJob
   */
  private $importerClass = ImportJob::class;

  /**
   * The DKAN Resource to import.
   *
   * @var \Drupal\common\DataResource
   */
  private $resource;

  /**
   * The jobstore factory service.
   *
   * @var \Drupal\common\Storage\JobStoreFactory
   *
   * @todo Can we remove this?
   */
  private $jobStoreFactory;

  /**
   * Database table factory service.
   *
   * @var \Drupal\datastore\Storage\DatabaseTableFactory
   */
  private $databaseTableFactory;

  /**
   * Create a resource service instance.
   *
   * @param \Drupal\common\DataResource $resource
   *   DKAN Resource.
   * @param \Drupal\common\Storage\JobStoreFactory $jobStoreFactory
   *   Jobstore factory.
   * @param \Drupal\datastore\Storage\DatabaseTableFactory $databaseTableFactory
   *   Database Table factory.
   */
  public function __construct(DataResource $resource, JobStoreFactory $jobStoreFactory, DatabaseTableFactory $databaseTableFactory) {
    $this->resource = $resource;
    $this->jobStoreFactory = $jobStoreFactory;
    $this->databaseTableFactory = $databaseTableFactory;
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
    $importer = $this->getImporter();
    $importer->run();

    $result = $this->getResult();
    $resource = $this->getResource();
    if ($result->getStatus() === Result::ERROR) {
      $datastore_resource = $resource->getDatastoreResource();
      $this->setLoggerFactory(\Drupal::service('logger.factory'));
      $this->error('Error importing resource id:%id path:%path message:%message', [
        '%id' => $datastore_resource->getId(),
        '%path' => $datastore_resource->getFilePath(),
        '%message' => $result->getError(),
      ]);
    }
    // If the import job finished successfully...
    elseif ($result->getStatus() === Result::DONE) {
      // Queue the imported resource for post-import processing.
      $post_import_queue = \Drupal::service('queue')->get('post_import');
      $post_import_queue->createItem($resource);
    }
  }

  /**
   * Get result.
   */
  public function getResult(): Result {
    $importer = $this->getImporter();
    return $importer->getResult();
  }

  /**
   * Build an Importer.
   *
   * @return \Drupal\datastore\Import
   *   Importer.
   *
   * @throws \Exception
   *   Throws exception if cannot create valid importer object.
   */
  public function getImporter(): ImportJob {
    $datastore_resource = $this->getResource()->getDatastoreResource();

    $delimiter = ",";
    if ($datastore_resource->getMimeType() == 'text/tab-separated-values') {
      $delimiter = "\t";
    }

    $importer = call_user_func([$this->importerClass, 'get'],
      $datastore_resource->getId(),
      $this->jobStoreFactory->getInstance(ImportJob::class),
      [
        "storage" => $this->getStorage(),
        "parser" => $this->getNonRecordingParser($delimiter),
        "resource" => $datastore_resource,
      ]
    );

    $importer->setTimeLimit(self::DEFAULT_TIMELIMIT);

    return $importer;
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
    $datastore_resource = $this->getResource()->getDatastoreResource();
    return $this->databaseTableFactory->getInstance($datastore_resource->getId(), ['resource' => $datastore_resource]);
  }

}
