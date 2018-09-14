<?php

namespace Drupal\dkan_harvest\Log;

use Drupal\dkan_harvest\Log;

class D8Log extends Log {

  function write($level, $action, $message) {
		$logEntry = $this->logEntry($action, $level, $message);
    // TODO: Write to the database.
  }

}
