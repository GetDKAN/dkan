<?php

namespace Drupal\dkan_harvest\Service;

use Drupal\dkan_harvest\Reverter;
use Harvest\Storage\Storage;

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
   * @param mixed $harvest_plan
   *   Harvest plan.
   *
   * @codeCoverageIgnore
   *
   * @return \Drupal\dkan_harvest\Reverter Reverter
   */
  public function newReverter($sourceId, Storage $hash_storage) {

    return new Reverter($sourceId, $hash_storage);

  }

}
