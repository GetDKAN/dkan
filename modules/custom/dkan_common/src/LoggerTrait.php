<?php

namespace Drupal\dkan_common;

use Drupal\Core\Logger\LoggerChannelFactory;

/**
 * LoggerTrait.
 */
trait LoggerTrait {
  /**
   * The logger factory service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactory
   */
  private $loggerService;

  /**
   * Setter.
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactory $loggerService
   *   Injected logger factory service.
   */
  public function setLoggerFactory(LoggerChannelFactory $loggerService) {
    $this->loggerService = $loggerService;
  }

  /**
   * Private.
   */
  private function log($loggerName, $message, $variables = []) {
    if ($this->loggerService) {
      $this->loggerService->get($loggerName)->error($message, $variables);
    }
  }

}
