<?php

namespace Drupal\dkan_metastore\Exception;

/**
 * Exception thrown when a payload is missing from an HTTP request.
 *
 * @package Drupal\dkan_metastore\Exception
 */
class MissingPayloadException extends MetastoreException {

  /**
   * {@inheritdoc}
   */
  public function httpCode(): int {
    return 400;
  }

}
