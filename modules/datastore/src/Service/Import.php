<?php

namespace Drupal\datastore\Service;

use CsvParser\Parser\Csv;
use Dkan\Datastore\Importer;
use Dkan\Datastore\Resource as DatastoreResource;
use Drupal\common\EventDispatcherTrait;
use Drupal\common\LoggerTrait;
use Drupal\common\Resource;
use Drupal\common\Storage\JobStoreFactory;
use Drupal\common\UrlHostTokenResolver;
use Drupal\datastore\Storage\DatabaseTable;
use Drupal\datastore\Storage\DatabaseTableFactory;
use Procrastinator\Result;

/**
 * Datastore import service.
 */
class Import {
  use LoggerTrait;
  use EventDispatcherTrait;

  const EVENT_CONFIGURE_PARSER = 'dkan_datastore_import_configure_parser';

  const DEFAULT_TIMELIMIT = 50;

  /**
   * The qualified class name of the importer to use.
   *
   * @var \Procrastinator\Job\AbstractPersistentJob
   */
  private $importerClass = Importer::class;

  /**
   * The resource object to import.
   *
   * @var \Dkan\Datastore\Resource
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
   * Constructor.
   */
  public function __construct(Resource $resource, JobStoreFactory $jobStoreFactory, DatabaseTableFactory $databaseTableFactory) {
    $this->initializeResource($resource);
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
   * Initialize resource.
   *
   * @param \Drupal\common\Resource $resource
   *   Resource.
   */
  protected function initializeResource(Resource $resource) {
    $this->resource = new DatastoreResource(
      md5($resource->getUniqueIdentifier()),
      UrlHostTokenResolver::resolve($resource->getFilePath()),
      $resource->getMimeType()
    );
  }

  /**
   * Getter.
   *
   * @return \Dkan\Datastore\Resource
   *   Resource.
   *
   * @codeCoverageIgnore
   */
  protected function getResource() : DatastoreResource {
    return $this->resource;
  }

  /**
   * Import.
   */
  public function import() {
    $importer = $this->getImporter();
    $importer->run();

    $result = $this->getResult();
    if ($result->getStatus() == Result::ERROR) {
      $this->setLoggerFactory(\Drupal::service('logger.factory'));
      $this->error('Error importing resource id:%id path:%path message:%message', [
        '%id' => $this->getResource()->getId(),
        '%path' => $this->getResource()->getFilePath(),
        '%message' => $result->getError(),
      ]);
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
   * @return \Dkan\Datastore\Importer
   *   Importer.
   *
   * @throws \Exception
   *   Throws exception if cannot create valid importer object.
   */
  public function getImporter(): Importer {
    $delimiter = ",";
    if ($this->resource->getMimeType() == 'text/tab-separated-values') {
      $delimiter = "\t";
    }

    $importer = call_user_func([$this->importerClass, 'get'],
      $this->resource->getId(),
      $this->jobStoreFactory->getInstance(Importer::class),
      [
        "storage" => $this->getStorage(),
        "parser" => $this->getNonRecordingParser($delimiter),
        "resource" => $this->resource,
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
    return $this->databaseTableFactory->getInstance($this->resource->getId(), ['resource' => $this->resource]);
  }

}
