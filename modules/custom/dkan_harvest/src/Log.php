<?php

namespace Drupal\dkan_harvest;

abstract class Log {

  protected $debug;
  protected $uuid;
  protected $id;
  protected $runId;


  function __construct($debug, $sourceId, $runId) {
    $this->debug = $debug ? TRUE : FALSE;
		$this->sourceId = $sourceId;
		$this->runId = $runId;
  }

  function write($level, $action, $message) {
  }

  function logEntry($level, $action, $message) {
		$date = date_create();
    return array(
			'source_id' => $this->sourceId,
			'run_id' => $this->runId,
			'action' => $action,
			'level' => $level,
			'message' => $message,
			'timestamp' => date_timestamp_get($date),
    );
	}
}
