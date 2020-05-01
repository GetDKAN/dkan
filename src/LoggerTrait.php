<?php

namespace Drupal\dkan;

use Drupal\Core\Logger\LoggerChannelFactory;
use Psr\Log\LogLevel;

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
  private function log($loggerName, $message, $variables = [], $level = LogLevel::ERROR) {
    if ($this->loggerService) {
      $this->loggerService->get($loggerName)->log($level, $message, $variables);
    }
  }

}
