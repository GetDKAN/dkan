<?php

namespace Drupal\dkan_harvest;

class D8Log extends Log {

  function write($level, $action, $message) {
		$logEntry = $this->logEntry($action, $level, $message);
    // TODO: Write to the database.
  }

}
