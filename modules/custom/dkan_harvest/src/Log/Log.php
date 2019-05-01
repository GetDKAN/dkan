<?php

namespace Drupal\dkan_harvest\Log;

/**
 *
 */
abstract class Log {

  protected $debug;
  protected $uuid;
  protected $id;

  protected $sourceId;
  protected $runId;

  /**
   *
   */
  public function __construct($debug, $sourceId, $runId) {
    $this->debug = $debug ? TRUE : FALSE;
    $this->sourceId = $sourceId;
    $this->runId = $runId;
  }

  /**
   *
   */
  abstract public function write($level, $action, $message);

  /**
   *
   */
  public function logEntry($level, $action, $message) {
    return [
      'source_id' => $this->sourceId,
      'run_id' => $this->runId,
      'action' => $action,
      'level' => $level,
      'message' => $message,
    // Use microtime?
      'timestamp' => \Drupal::time()->getCurrentTime(),
    ];
  }

}
