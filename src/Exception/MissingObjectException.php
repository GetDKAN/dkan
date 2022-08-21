<?php

namespace Drupal\dkan\Exception;

/**
 * Exception thrown when a metastore item could not be found for an identifier.
 *
 * @package Drupal\dkan\Exception
 */
class MissingObjectException extends MetastoreException {

  /**
   * {@inheritdoc}
   */
  public function httpCode(): int {
    return 412;
  }

}
