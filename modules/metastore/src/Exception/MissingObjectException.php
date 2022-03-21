<?php

namespace Drupal\metastore\Exception;

/**
 * Exception thrown when a metastore item could not be found for an identifier.
 *
 * @package Drupal\metastore\Exception
 */
class MissingObjectException extends MetastoreException {

  /**
   * {@inheritdoc}
   */
  public function httpCode(): int {
    return 412;
  }

}
