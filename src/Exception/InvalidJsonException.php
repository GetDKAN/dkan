<?php

namespace Drupal\dkan\Exception;

/**
 * Exception thrown when metastore item JSON validation failed.
 *
 * @package Drupal\dkan\Exception
 */
class InvalidJsonException extends MetastoreException {

  /**
   * {@inheritdoc}
   */
  public function httpCode(): int {
    return 415;
  }

}
