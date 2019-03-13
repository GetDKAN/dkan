<?php

namespace Drupal\dkan_harvest\Log;

abstract class Log {

  protected $debug;
  protected $uuid;
  protected $id;

  protected $sourceId;
  protected $runId;

  function __construct($debug, $sourceId, $runId) {
    $this->debug = $debug ? TRUE : FALSE;
    $this->sourceId = $sourceId;
    $this->runId = $runId;
  }

  abstract function write($level, $action, $message);

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
