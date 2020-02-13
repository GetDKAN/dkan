<?php

namespace Drupal\dkan_metastore\Exception;

/**
 * Class ExistingObjectException.
 *
 * @package Drupal\dkan_metastore\Exception
 */
class ExistingObjectException extends MetastoreException {

  /**
   * {@inheritdoc}
   */
  public function httpCode(): int {
    return 409;
  }

}
