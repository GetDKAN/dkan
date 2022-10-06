<?php

namespace Drupal\dkan\Exception;

/**
 * Metastore's base exception class.
 *
 * @package Drupal\dkan\Exception
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
