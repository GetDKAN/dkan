<?php

namespace Drupal\dkan_harvest;

abstract class Revert {

  protected $log;
  public $sourceId;
  protected $runId;

  function __construct($log, $sourceId) {
    $this->log = $log;
    $this->sourceId = $sourceId;
  }

  function run() {
  }

}
