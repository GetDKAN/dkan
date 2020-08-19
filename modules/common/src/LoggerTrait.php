<?php

namespace Drupal\common;

use Drupal\Core\Logger\LoggerChannelFactory;
use Psr\Log\LogLevel;

/**
 * LoggerTrait.
 */
trait LoggerTrait {

  /**
   * The logger channel name, with a default value of 'dkan'.
   *
   * Classes using this trait cannot redeclare this property, but are free to
   * override its value anywhere, e.g. in a constructor.
   *
   * @var string
   */
  private $loggerName = 'dkan';

  /**
   * The logger factory service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactory
   */
  private $loggerService;

  private $debug = FALSE;

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

  /**
   * Private.
   */
  private function error(string $message, array $context = []) {

    if ($this->loggerService) {
      $this->loggerService->get($this->loggerName)->error($message, $context);
    }
  }

  /**
   * Private.
   */
  private function warning(string $message, array $context = []) {

    if ($this->loggerService) {
      $this->loggerService->get($this->loggerName)->warning($message, $context);
    }
  }

  /**
   * Private.
   */
  private function notice(string $message, array $context = []) {

    if ($this->loggerService) {
      $this->loggerService->get($this->loggerName)->notice($message, $context);
    }
  }

  /**
   * Private.
   */
  private function showDebug() {
    $this->debug = TRUE;
  }

  /**
   * Private.
   */
  private function debug(string $message = "", array $context = []) {
    if ($this->loggerService && $this->debug) {
      $m = "@class @function: " . $message;
      $c = array_merge($context,
        [
          '@class' => static::class,
          '@function' => debug_backtrace()[1]['function'],
          '@message' => $message,
        ]
      );

      $this->loggerService->get($this->loggerName)->notice($m, $c);
    }
  }

}
