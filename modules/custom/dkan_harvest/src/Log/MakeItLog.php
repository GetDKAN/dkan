<?php

namespace Drupal\dkan_harvest\Log;

/**
 *
 */
trait MakeItLog {
  protected $logger;

  /**
   *
   */
  public function setLogger($logger) {
    $this->logger = $logger;
  }

  /**
   *
   */
  protected function log($level, $action, $message) {
    if ($this->logger) {
      $this->logger->write($level, $action, $message);
    }
  }

}
