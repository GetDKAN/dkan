<?php

namespace Drupal\dkan_datastore\Service;

use CsvParser\Parser\Csv;
use Dkan\Datastore\Importer;
use Drupal\dkan_datastore\Storage\DatabaseTable;
use Drupal\dkan_datastore\Storage\JobStoreFactory;
use Procrastinator\Result;
use Dkan\Datastore\Resource;
use Drupal\dkan_datastore\Storage\DatabaseTableFactory;

/**
 * Class Import.
 */
class Import {
  const DEFAULT_TIMELIMIT = 50;

  private $resource;
  private $jobStoreFactory;
  private $databaseTableFactory;

  /**
   * Constructor.
   */
  public function __construct(Resource $resource, JobStoreFactory $jobStoreFactory, DatabaseTableFactory $databaseTableFactory) {
    $this->resource = $resource;
    $this->jobStoreFactory = $jobStoreFactory;
    $this->databaseTableFactory = $databaseTableFactory;
  }

  /**
   * Import.
   */
  public function import() {
    $importer = $this->getImporter();
    $importer->run();
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

    $importer = Importer::get($this->resource->getId(),
      $this->jobStoreFactory->getInstance(Importer::class),
      [
        "storage" => $this->getStorage(),
        "parser" => Csv::getParser(),
        "resource" => $this->resource,
      ]
    );

    $importer->setTimeLimit(self::DEFAULT_TIMELIMIT);

    return $importer;
  }

  /**
   * Build a database table storage object.
   *
   * @return \Drupal\dkan_datastore\Storage\DatabaseTable
   *   DatabaseTable storage object.
   */
  public function getStorage(): DatabaseTable {
    return $this->databaseTableFactory->getInstance(json_encode($this->resource));
  }

}
