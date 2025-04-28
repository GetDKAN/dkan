<?php

namespace Drupal\datastore\Service;

use Drupal\dkan\DataResource;

/**
 * Resource processor to be run after import.
 */
interface ResourceProcessorInterface {

  /**
   * Process the given datastore resource.
   *
   * @param \Drupal\dkan\DataResource $resource
   *   Datastore resource.
   */
  public function process(DataResource $resource): void;

}
