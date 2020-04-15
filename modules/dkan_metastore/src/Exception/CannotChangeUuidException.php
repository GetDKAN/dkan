<?php

namespace Drupal\dkan_metastore\Exception;

/**
 * Class CannotChangeUuidException.
 *
 * @package Drupal\dkan_metastore\Exception
 */
class CannotChangeUuidException extends MetastoreException {

  /**
   * {@inheritdoc}
   */
  public function httpCode(): int {
    return 409;
  }

}
