<?php

namespace Drupal\datastore\Service;

use Drupal\common\DataResource;

/**
 * Resource processor to be run after import.
 */
interface ResourceProcessorInterface {

  /**
   * Process the given datastore resource.
   *
   * @param \Drupal\common\DataResource $resource
   *   Datastore resource.
   */
  public function process(DataResource $resource): void;

}
