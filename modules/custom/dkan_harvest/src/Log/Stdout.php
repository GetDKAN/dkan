<?php

namespace Drupal\dkan_harvest;

class Stdout extends Log {

  function write($level, $action, $message) {
    if (!$this->debug && $level == 'DEBUG') return;
		$logEntry = $this->logEntry($action, $level, $message);
		print($logEntry['message'] . "\n");
  }

}
