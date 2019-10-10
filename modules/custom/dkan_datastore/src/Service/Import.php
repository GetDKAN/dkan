<?php

namespace Drupal\dkan_datastore\Service;

use CsvParser\Parser\Csv;
use Dkan\Datastore\Importer;
use Drupal\dkan_datastore\Storage\DatabaseTable;
use Drupal\dkan_datastore\Storage\JobStore;
use Procrastinator\Result;
use Dkan\Datastore\Resource;
use Drupal\dkan_datastore\Storage\DatabaseTableFactory;

/**
 * Class Import.
 */
class Import {
  const DEFAULT_TIMELIMIT = 50;

  private $resource;
  private $jobStore;
  private $databaseTableFactory;

  /**
   * Constructor.
   */
  public function __construct(Resource $resource, JobStore $jobStore, DatabaseTableFactory $databaseTableFactory) {
    $this->resource = $resource;
    $this->jobStore = $jobStore;
    $this->databaseTableFactory = $databaseTableFactory;
  }

  /**
   * Import.
   */
  public function import() {
    $importer = $this->getImporter();
    $importer->run();
    $this->jobStore->store($this->resource->getId(), $importer);
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
  private function getImporter(): Importer {
    if (!$importer = $this->getStoredImporter()) {
      $importer = new Importer($this->resource, $this->getStorage(), Csv::getParser());
      $importer->setTimeLimit(self::DEFAULT_TIMELIMIT);
      $this->jobStore->store($this->resource->getId(), $importer);
    }
    if (!($importer instanceof Importer)) {
      throw new \Exception("Could not load importer for resource {$this->resource->getId()}");
    }
    return $importer;
  }

  /**
   * Get a stored importer.
   *
   * @return \Dkan\Datastore\Importer|Null
   *   Importer object.
   */
  private function getStoredImporter(): ?Importer {
    if ($importer = $this->jobStore->retrieve($this->resource->getId(), Importer::class)) {
      return $importer;
    }
    return NULL;
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
