<?php

namespace Drupal\common;

use Drupal\Core\Logger\RfcLogLevel;

trait RemovalLogger {
  public function log(string $message = '', array $params = []) {
    $steps = debug_backtrace(0, 2);
    array_shift($steps);
    $backtrace = '';
    foreach ($steps as $step) {
      if (isset($step['class'])) {
        $backtrace .= '=>' . $step['class'] . $step['type'] . $step['function'];
      }
    }

    $code_source = $step['class'] . '::' . $step['function'] . ' L' . __LINE__;
    \Drupal::logger(__CLASS__)->log(RfcLogLevel::INFO, "$message ($code_source)", $params);
  }

}
