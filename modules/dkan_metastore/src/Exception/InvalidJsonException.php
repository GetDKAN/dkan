<?php

namespace Drupal\dkan_metastore\Exception;

/**
 * Exception thrown when metastore item JSON validation failed.
 *
 * @package Drupal\dkan_metastore\Exception
 */
class InvalidJsonException extends MetastoreException {

  /**
   * {@inheritdoc}
   */
  public function httpCode(): int {
    return 415;
  }

}
