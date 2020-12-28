<?php

namespace Drupal\common\Storage;

/**
 * Trait SanitizedMessageTrait.
 */
trait SanitizedMessageTrait {

  /**
   * Create a minimal error message that does not leak database information.
   */
  private function sanitizedErrorMessage(string $unsanitizedMessage) {
    // Insert portions of exception messages you want caught here.
    $messages = [
      'Column not found',
    ];
    foreach ($messages as $message) {
      if (strpos($unsanitizedMessage, $message) !== FALSE) {
        return $message . ".";
      }
    }
    return "Database internal error.";
  }

}
