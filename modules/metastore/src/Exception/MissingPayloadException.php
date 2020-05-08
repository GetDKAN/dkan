<?php

namespace Drupal\metastore\Exception;

/**
 * Class MissingPayloadException.
 *
 * @package Drupal\metastore\Exception
 */
class MissingPayloadException extends MetastoreException {

  /**
   * {@inheritdoc}
   */
  public function httpCode(): int {
    return 400;
  }

}
