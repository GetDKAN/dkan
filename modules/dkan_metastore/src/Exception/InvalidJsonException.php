<?php

namespace Drupal\dkan_metastore\Exception;

/**
 * Class InvalidJsonException.
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
