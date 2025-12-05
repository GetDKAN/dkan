<?php

namespace Drupal\dkan_metastore\Exception;

/**
 * Exception thrown when a metastore item could not be found for an identifier.
 *
 * @package Drupal\dkan_metastore\Exception
 */
class MissingObjectException extends MetastoreException {

  /**
   * {@inheritdoc}
   */
  public function httpCode(): int {
    return 404;
  }

}
