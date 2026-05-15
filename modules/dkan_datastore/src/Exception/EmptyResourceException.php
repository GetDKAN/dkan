<?php

namespace Drupal\dkan_datastore\Exception;

/**
 * Exception for empty datastore resources.
 */
class EmptyResourceException extends \Exception {

  /**
   * Constructor.
   *
   * @param string $resourceId
   *   The unique identifier for the resource that is empty.
   */
  public function __construct(string $resourceId) {
    parent::__construct('The resource ' . $resourceId . ' does not contain any data.');
  }

}
