<?php

namespace Drupal\dkan_datastore\Service;

use Drupal\dkan_common\DataResource;

/**
 * Resource processor to be run after import.
 */
interface ResourceProcessorInterface {

  /**
   * Process the given datastore resource.
   *
   * @param \Drupal\dkan_common\DataResource $resource
   *   Datastore resource.
   */
  public function process(DataResource $resource): void;

}
