<?php

namespace Drupal\metastore\Exception;

/**
 * Exception thrown when metastore item already exists with a given identifier.
 *
 * @package Drupal\metastore\Exception
 */
class ExistingObjectException extends MetastoreException {

  /**
   * {@inheritdoc}
   */
  public function httpCode(): int {
    return 409;
  }

}
