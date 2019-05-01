<?php

namespace Drupal\dkan_harvest\Log;

/**
 *
 */
class Stdout extends Log {

  /**
   *
   */
  public function write($level, $action, $message) {
    if (!$this->debug && $level == 'DEBUG') {
      return;
    }
    $logEntry = $this->logEntry($action, $level, $message);
    print($logEntry['message'] . "\n");
  }

}
