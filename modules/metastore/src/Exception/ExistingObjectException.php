<?php

namespace Drupal\metastore\Exception;

/**
 * Class ExistingObjectException.
 *
 * @package Drupal\metastore\Exception
 */
class ExistingObjectException extends MetastoreException {

  /**
   * {@inheritdoc}
   */
  public function httpCode(): int {
    return 409;
  }

}
