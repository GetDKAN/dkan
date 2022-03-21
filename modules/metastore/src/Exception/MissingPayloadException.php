<?php

namespace Drupal\metastore\Exception;

/**
 * Exception thrown when a payload is missing from an HTTP request.
 *
 * @package Drupal\metastore\Exception
 */
class MissingPayloadException extends MetastoreException {

  /**
   * {@inheritdoc}
   */
  public function httpCode(): int {
    return 400;
  }

}
