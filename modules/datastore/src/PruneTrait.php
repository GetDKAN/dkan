<?php

namespace Drupal\datastore;

//use Drupal\datastore\Service\ResourceLocalizer;

/**
 * PruneTrait.
 */
trait PruneTrait {
  // /**
  //  * Resource localizer for handling remote resource URLs.
  //  *
  //  * @var \Drupal\datastore\Service\ResourceLocalizer
  //  */
  // private $resourceLocalizer;

  /**
   * Delete jobstore entries related to a datastore.
   */
  public function jobstorePrune($uuid) {
    if (!isset($this->resourceLocalizer)) {
      \Drupal::logger('datastore')->error('ResourceLocalizer is not set.');
      return;
    }
    $resource = $this->resourceLocalizer->get($uuid);
    $ref_uuid = $resource->getUniqueIdentifier();
    $ref_uuid_f = substr(str_replace('__', '_', $ref_uuid), 0, -11);
    $ref_uuid_i = md5($ref_uuid);

    try {
      $query = \Drupal::database()->delete('jobstore_dkan_datastore_importer');
      $query->condition('ref_uuid', $ref_uuid_i);
      $query->execute();
    }
    catch (\Exception $e) {
      \Drupal::logger('datastore')->error('Not able to delete the importer job with ref_uuid %id', ['%id' => $ref_uuid_i]);
      \Drupal::logger('datastore')->error($e->getMessage());
    }

    try {
      $query = \Drupal::database()->delete('jobstore_filefetcher_filefetcher');
      $query->condition('ref_uuid', $ref_uuid_f);
      $query->execute();
    }
    catch (\Exception $e) {
      \Drupal::logger('datastore')->error('Not able to delete the file fetcher job with ref_uuid %id', ['%id' => $ref_uuid_f]);
      \Drupal::logger('datastore')->error($e->getMessage());
    }

  }

}
