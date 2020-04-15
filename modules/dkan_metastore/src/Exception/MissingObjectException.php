<?php

namespace Drupal\dkan_metastore\Exception;

/**
 * Class MissingObjectException.
 *
 * @package Drupal\dkan_metastore\Exception
 */
class MissingObjectException extends MetastoreException {

  /**
   * {@inheritdoc}
   */
  public function httpCode(): int {
    return 412;
  }

}
