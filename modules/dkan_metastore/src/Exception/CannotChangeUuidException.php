<?php

namespace Drupal\dkan_metastore\Exception;

/**
 * Exception thrown when unable to change UUID for a metastore item.
 *
 * @package Drupal\dkan_metastore\Exception
 */
class CannotChangeUuidException extends MetastoreException {

  /**
   * {@inheritdoc}
   */
  public function httpCode(): int {
    return 409;
  }

}
