<?php

namespace Drupal\datastore\Exception;

/**
 * Class LastResortException.
 *
 * @package FileFetcher
 */
class LocalizeException extends \Exception {

  /**
   * Constructor.
   *
   * @param string $operation
   *   Localize operation name.
   * @param string $filename
   *   Filename for localizing.
   */
  public function __construct(string $operation, string $filename) {
    $message = sprintf("Error %s file: %s.", $operation, $filename);
    parent::__construct($message, 0);
  }

}
