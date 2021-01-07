<?php

namespace Drupal\datastore;

/**
 * PruneTrait.
 */
trait PruneTrait {

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
    $jobs = [
      [
        "id" => substr(str_replace('__', '_', $ref_uuid), 0, -11),
        "table" => "jobstore_filefetcher_filefetcher",
      ],
      [
        "id" => md5($ref_uuid),
        "table" => "jobstore_dkan_datastore_importer",
      ],
    ];

    try {
      foreach ($jobs as $job) {
        $query = \Drupal::database()->delete($job['table'])->condition('ref_uuid', $job['id'])->execute();
      }
    }
    catch (\Exception $e) {
      \Drupal::logger('datastore')->error('Not able to delete the importer job with ref_uuid %id', ['%id' => $job['id']]);
      \Drupal::logger('datastore')->error($e->getMessage());
    }
  }

}
