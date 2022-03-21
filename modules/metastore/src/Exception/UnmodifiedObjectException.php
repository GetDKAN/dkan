<?php

namespace Drupal\metastore\Exception;

/**
 * Exception thrown when an update request doesn't change a metastore item.
 *
 * @package Drupal\metastore\Exception
 */
class UnmodifiedObjectException extends MetastoreException {

  /**
   * {@inheritdoc}
   */
  public function httpCode(): int {
    return 403;
  }

}
