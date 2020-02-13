<?php

namespace Drupal\dkan_metastore\Exception;

/**
 * Class MissingPayloadException.
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
