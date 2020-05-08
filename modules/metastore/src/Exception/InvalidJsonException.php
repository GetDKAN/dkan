<?php

namespace Drupal\metastore\Exception;

/**
 * Class InvalidJsonException.
 *
 * @package Drupal\metastore\Exception
 */
class InvalidJsonException extends MetastoreException {

  /**
   * {@inheritdoc}
   */
  public function httpCode(): int {
    return 415;
  }

}
