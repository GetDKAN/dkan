<?php

namespace Drupal\datastore\Commands;

use Drupal\datastore\Service as Datastore;
use Drupal\common\DatasetInfo;
use Drush\Commands\DrushCommands;

/**
 * A Drush commandfile.
 */
class ReimportCommands extends DrushCommands {

  /**
   * The datastore service.
   *
   * @var \Drupal\datastore\Service
   */
  protected Datastore $datastoreService;

  /**
   * Dataset info service.
   *
   * @var \Drupal\common\DatasetInfo
   */
  protected DatasetInfo $datasetInfo;

  /**
   * Constructor for DkanDatastoreCommands.
   */
  public function __construct(
    Datastore $datastore_service,
    DatasetInfo $dataset_info
  ) {
    $this->datastoreService = $datastore_service;
    $this->datasetInfo = $dataset_info;
    parent::__construct();
  }

  /**
   * Re-import distribution based on dataset UUID.
   *
   * @param string $uuid
   *   The UUID of the dataset.
   *
   * @usage dkan:datastore-reimport C66A2DF6-7BB2-40D2-8551-6E1104BBCC57
   *   Drop and import the distribution based on the dataset UUID.
   *
   * @command dkan:datastore-reimport
   *
   * Sample bash:
   * for i in `cat ./uuid-list.txt`
   * do
   *   dktl drush dkan:datastore-reimport $i
   * done
   */
  public function datastoreReimport(string $uuid) {
    // Find distributions for the given UUID.
    if ($info = $this->datasetInfo->gather($uuid) ?? FALSE) {
      if ($distributions = $info['latest_revision']['distributions'] ?? FALSE) {
        // Reimport whatever distributions we found.
        $this->logger()
          ->success(count($info) . ' distribution(s) found for ' . $uuid);
        $this->reimportDistributions($distributions);
      }
      else {
        $this->logger()->error('Unable to find distributions for ' . $uuid);
      }
    }
    else {
      $this->logger()->error('Unable to find dataset info for ' . $uuid);
    }
  }

  /**
   * Perform the reimport of given distributions.
   *
   * Each distribution will be dropped from the database and then re-added.
   *
   * @param array $distributions
   *   The distributions to reimport.
   */
  protected function reimportDistributions(array $distributions) {
    foreach ($distributions as $distribution) {
      if ($resource_id = $distribution['resource_id'] ?? FALSE) {
        $this->logger()
          ->success('Reimporting distribution: ' . $resource_id);
        $this->datastoreService->drop($resource_id);
        $result = $this->datastoreService->import($resource_id);
        $status = $result['Import'] ? $result['Import']->getStatus() : 'failed, resource not found';
        $this->logger->notice("Ran import for $resource_id; status: $status");
      }
    }
  }

}
