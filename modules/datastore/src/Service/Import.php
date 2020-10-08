<?php

namespace Drupal\datastore\Service;

use CsvParser\Parser\Csv;
use Dkan\Datastore\Importer;
use Dkan\Datastore\Resource as DatastoreResource;
use Drupal\common\LoggerTrait;
use Drupal\common\Resource;
use Drupal\common\Storage\JobStoreFactory;
use Drupal\common\UrlHostTokenResolver;
use Drupal\datastore\Storage\DatabaseTable;
use Drupal\datastore\Storage\DatabaseTableFactory;
use Procrastinator\Result;

/**
 * Class Import.
 */
class Import {
  use LoggerTrait;

  const DEFAULT_TIMELIMIT = 50;

  private $resource;
  private $jobStoreFactory;
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
      $this->error("Error importing resource id:%id path:%path", [
        '%id' => $this->getResource()->getId(),
        '%path' => $this->getResource()->getFilePath(),
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

    $importer = Importer::get($this->resource->getId(),
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
    $parser = Csv::getParser($delimiter);
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
