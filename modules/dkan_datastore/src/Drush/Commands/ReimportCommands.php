<?php

namespace Drupal\dkan_datastore\Drush\Commands;

use Drupal\dkan_common\DatasetInfo;
use Drupal\dkan_datastore\DatastoreService;
use Drush\Attributes as CLI;
use Drush\Commands\AutowireTrait;
use Drush\Commands\DrushCommands;

/**
 * Drush command file for datastore reimportation.
 *
 * @codeCoverageIgnore
 */
final class ReimportCommands extends DrushCommands {

  use AutowireTrait;

  public function __construct(
    protected DatastoreService $datastoreService,
    protected DatasetInfo $datasetInfo,
  ) {
    parent::__construct();
  }

  /**
   * Re-import distribution based on dataset UUID.
   *
   * Sample bash:
   * ```
   * for i in `cat ./uuid-list.txt`
   * do
   *   ddev drush dkan:datastore:reimport $i
   * done
   * ```
   */
  #[CLI\Command(name: 'dkan:datastore:reimport', description: 'Re-import resources based on dataset UUID.')]
  #[CLI\Argument(name: 'uuid', description: 'The UUID of the dataset.')]
  #[CLI\Usage(name: 'dkan:datastore:reimport cedcd327-4e5d-43f9-8eb1-c11850fa7c55', description: 'Drop and import the distribution based on the dataset UUID.')]
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
        $result = $this->datastoreService->import($resource_id, FALSE, $distribution['resource_version'] ?? NULL);
        $status = $result['ImportService'] ? $result['ImportService']->getStatus() : 'failed, resource not found';
        $this->logger()->notice("Ran import for $resource_id; status: $status");
      }
    }
  }

}
