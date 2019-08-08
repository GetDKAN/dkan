<?php

namespace Drupal\dkan_harvest\Service;

use Drupal\dkan_harvest\Reverter;
use Harvest\Storage\Storage;
use Drupal\dkan_harvest\Storage\File;

use Harvest\ETL\Factory as EtlFactory;

/**
 * Factory class for bits and pieces of dkan_harvest.
 *
 * Coverage is mostly ignored for this class since it's mostly a proxy to
 * initialise new instances.
 *
 * @codeCoverageIgnore
 */
class Factory {

  /**
   * New instance of Reverter.
   *
   * @return \Drupal\dkan_harvest\Reverter
   *   Reverter.
   *
   * @codeCoverageIgnore
   */
  public function newReverter($sourceId, Storage $hash_storage) {
    return new Reverter($sourceId, $hash_storage);
  }

  /**
   * Get plan storage.
   *
   * @return \Drupal\dkan_harvest\Storage\File
   *   File.
   */
  public function getPlanStorage(): Storage {
    $path = \Drupal::service('file_system')->realpath(file_default_scheme() . "://");
    return new File("{$path}/dkan_harvest/plans");
  }

  /**
   * Get Storage.
   *
   * @param mixed $id
   *   Id.
   * @param mixed $type
   *   Type.
   *
   * @return \Drupal\dkan_harvest\Storage\File
   *   File.
   */
  public function getStorage($id, $type) {
    $path = \Drupal::service('file_system')->realpath(file_default_scheme() . "://");
    return new File("{$path}/dkan_harvest/{$id}-{$type}");
  }

  /**
   * Get Harvestter from id and harvest plan.
   *
   * @param string $id
   *   Id.
   * @param object $harvestPlan
   *   Harvest plan.
   *
   * @return \Harvest\Harvester
   *   Harvester.
   */
  public function getHarvester(string $id, $harvestPlan = NULL): Harvester {

    if (empty($harvestPlan)) {
      $harvestPlan = json_decode(
      $this->getPlanStorage()
        ->retrieve($id)
      );
    }

    return new Harvester(new EtlFactory($harvestPlan,
      $this->getStorage($id, "item"),
      $this->getStorage($id, "hash")));
  }

}
