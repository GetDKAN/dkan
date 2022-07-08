<?php

namespace Drupal\datastore\Service;

use Drupal\common\Resource;

/**
 * Resource processor to be run after import.
 */
interface ResourceProcessorInterface {

  /**
   * Process the given datastore resource.
   *
   * @param \Drupal\common\Resource $resource
   *   Datastore resource.
   */
  public function process(Resource $resource): void;

}
