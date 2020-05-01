<?php

namespace Drupal\metastore\Exception;

/**
 * Class UnmodifiedObjectException.
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
