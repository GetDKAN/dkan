<?php

namespace Drupal\datastore\Commands;

/**
 * Datastore Trait to assist Drush commands.
 *
 * @codeCoverageIgnore
 */
trait Helper {

  /**
   * Delete jobstore entries related to a datastore.
   */
  public function jobstorePrune($uuid, $keep = FALSE) {
    if (!isset($this->resourceLocalizer)) {
      \Drupal::logger('datastore')->error('ResourceLocalizer is not set.');
      return;
    }
    $resource = $this->resourceLocalizer->get($uuid);
    $ref_uuid = $resource->getUniqueIdentifier();
    $jobs[] = ["id" => md5($ref_uuid), "table" => "jobstore_dkan_datastore_importer"];
    if (!$keep) {
      $jobs[] = [
        "id" => substr(str_replace('__', '_', $ref_uuid), 0, -11),
        "table" => "jobstore_filefetcher_filefetcher",
      ];
    }

    try {
      foreach ($jobs as $job) {
        \Drupal::database()->delete($job['table'])->condition('ref_uuid', $job['id'])->execute();
        $this->logger('datastore')->notice("Successfully removed the {$job['table']} record for ref_uuid {$job['id']}.");
      }
    }
    catch (\Exception $e) {
      $this->logger('datastore')->error("Failed to delete the jobstore record for ref_uuid {$job['id']}.", $e->getMessage());
    }
  }

}
