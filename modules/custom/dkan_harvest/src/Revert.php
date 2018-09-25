<?php

namespace Drupal\dkan_harvest;

abstract class Revert {

  protected $log;
  public $sourceId;
  protected $runId;

  function __construct($log, $sourceId, $runId) {
    $this->log = $log;
    $this->sourceId = $sourceId;
    $this->runId = $runId;
  }

  function run() {
  }

}
