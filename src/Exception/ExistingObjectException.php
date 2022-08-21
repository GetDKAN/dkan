<?php

namespace Drupal\dkan\Exception;

/**
 * Exception thrown when metastore item already exists with a given identifier.
 *
 * @package Drupal\dkan\Exception
 */
class ExistingObjectException extends MetastoreException {

  /**
   * {@inheritdoc}
   */
  public function httpCode(): int {
    return 409;
  }

}
