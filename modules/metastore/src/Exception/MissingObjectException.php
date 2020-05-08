<?php

namespace Drupal\metastore\Exception;

/**
 * Class MissingObjectException.
 *
 * @package Drupal\metastore\Exception
 */
class MissingObjectException extends MetastoreException {

  /**
   * {@inheritdoc}
   */
  public function httpCode(): int {
    return 412;
  }

}
