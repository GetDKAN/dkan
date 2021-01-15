<?php

namespace Drupal\datastore\Commands;

/**
 * Datastore Trait to assist Drush commands.
 *
 * @codeCoverageIgnore
 */
trait Helper {

  /**
   * Delete jobstore entries related to a datastore import.
   */
  public function jobstorePruneImporter($uuid) {
    if (!isset($this->resourceLocalizer)) {
      \Drupal::logger('datastore')->error('ResourceLocalizer is not set.');
      return;
    }
    $resource = $this->resourceLocalizer->get($uuid);
    if ($resource) {
      $ref_uuid = $resource->getUniqueIdentifier();
      $id = md5($ref_uuid);
      try {
        \Drupal::database()->delete('jobstore_dkan_datastore_importer')->condition('ref_uuid', $id)->execute();
        $this->logger('datastore')->notice("Successfully removed the jobstore record for ref_uuid {$id}.");
      }
      catch (\Exception $e) {
        $this->logger('datastore')->error("Failed to delete the jobstore record for ref_uuid {$id}.", $e->getMessage());
      }
    }
    else {
      $this->logger('datastore')->error('Unable to get the unique identifier.');
    }
  }

  /**
   * Delete jobstore entries related to a datastore's local file.
   */
  public function jobstorePruneFilefetcher($uuid) {
    if (!isset($this->resourceLocalizer)) {
      \Drupal::logger('datastore')->error('ResourceLocalizer is not set.');
      return;
    }

    $resource = $this->resourceLocalizer->get($uuid);
    if ($resource) {
      $ref_uuid = $resource->getUniqueIdentifier();
      $id = substr(str_replace('__', '_', $ref_uuid), 0, -11);
      try {
        \Drupal::database()->delete('jobstore_filefetcher_filefetcher')->condition('ref_uuid', $id)->execute();
        $this->logger('datastore')->notice("Successfully removed the jobstore record for ref_uuid {$id}.");
      }
      catch (\Exception $e) {
        $this->logger('datastore')->error("Failed to delete the jobstore record for ref_uuid {$id}.", $e->getMessage());
      }
    }
    else {
      $this->logger('datastore')->error('Unable to get the unique identifier.');
    }
  }

}
