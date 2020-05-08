<?php

namespace Drupal\metastore\Exception;

/**
 * Class CannotChangeUuidException.
 *
 * @package Drupal\metastore\Exception
 */
class CannotChangeUuidException extends MetastoreException {

  /**
   * {@inheritdoc}
   */
  public function httpCode(): int {
    return 409;
  }

}
