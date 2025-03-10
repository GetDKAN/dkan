<?php

namespace Drupal\datastore\Commands;

use Drupal\common\DatasetInfo;
use Drupal\datastore\DatastoreService;
use Drush\Commands\DrushCommands;

/**
 * Drush command file for data store reimportation.
 *
 * @codeCoverageIgnore
 */
class ReimportCommands extends DrushCommands {

  /**
   * The datastore service.
   *
   * @var \Drupal\datastore\Service
   */
  protected DatastoreService $datastoreService;

  /**
   * Dataset info service.
   */
  protected DatasetInfo $datasetInfo;

  /**
   * Constructor for DkanDatastoreCommands.
   *
   * @param \Drupal\datastore\DatastoreService $datastore_service
   *   The dkan.datastore.service service.
   * @param \Drupal\common\DatasetInfo $dataset_info
   *   Dataset information service.
   */
  public function __construct(
    DatastoreService $datastore_service,
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
   * @command dkan:datastore:reimport
   *
   * Sample bash:
   * for i in `cat ./uuid-list.txt`
   * do
   *   ddev drush dkan:datastore:reimport $i
   * done
   */
  public function datastoreReimport(string $uuid) {
    // Find distributions for the given UUID.
    if ($info = $this->datasetInfo->gather($uuid) ?? FALSE) {
      if ($distributions = $info['latest_revision']['distributions'] ?? FALSE) {
        // Reimport whatever distributions we found.
        $this->reimportDistributions($distributions);
        $this->logger()->notice(count($info) . ' distribution(s) found for ' . $uuid);
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
        $this->datastoreService->drop($resource_id);
        $this->logger()->notice('Reimporting distribution: ' . $resource_id);
        $result = $this->datastoreService->import($resource_id);
        $status = $result['ImportService'] ? $result['ImportService']->getStatus() : 'failed, resource not found';
        $this->logger()->notice("Ran import for $resource_id; status: $status");
      }
    }
  }

}
