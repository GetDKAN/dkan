<?php

namespace Drupal\dkan_metastore\Exception;

/**
 * Exception thrown when an update request doesn't change a metastore item.
 *
 * @package Drupal\dkan_metastore\Exception
 */
class UnmodifiedObjectException extends MetastoreException {

  /**
   * {@inheritdoc}
   */
  public function httpCode(): int {
    return 403;
  }

}
