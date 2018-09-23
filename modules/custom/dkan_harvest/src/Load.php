<?php

namespace Drupal\dkan_harvest;

abstract class Load {

  protected $sourceId;
  protected $runId;
  protected $log;
  protected $config;

  function __construct($log, $config, $sourceId, $runId) {
    $this->log = $log;
    $this->sourceId = $sourceId;
    $this->runId = $runId;
    $this->config = $config;
  }

  function run($items) {
  }

  function revert($items) {
  }

}
