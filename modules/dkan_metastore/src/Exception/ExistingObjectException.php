<?php

namespace Drupal\dkan_metastore\Exception;

/**
 * Exception thrown when metastore item already exists with a given identifier.
 *
 * @package Drupal\dkan_metastore\Exception
 */
class ExistingObjectException extends MetastoreException {

  /**
   * {@inheritdoc}
   */
  public function httpCode(): int {
    return 409;
  }

}
