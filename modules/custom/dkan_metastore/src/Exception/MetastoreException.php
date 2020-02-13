<?php

namespace Drupal\dkan_metastore\Exception;

/**
 * Metastore's base exception class.
 *
 * @package Drupal\dkan_metastore\Exception
 */
abstract class MetastoreException extends \Exception {

  /**
   * Returns the appropriate http error code.
   *
   * @return int
   *   The http code.
   */
  abstract public function httpCode() : int;

}
