<?php

namespace Drupal\dkan_harvest;

use Drupal\dkan_harvest\Extract;
use Drupal\dkan_harvest\Extract\DataJson;
use Drupal\dkan_harvest\Transform;
use Drupal\dkan_harvest\Transform\Filter;
use Drupal\dkan_harvest\Transform\Override;
use Drupal\dkan_harvest\Load;
use Drupal\dkan_harvest\Load\Dkan8;
use Drupal\dkan_harvest\Log;
use Drupal\dkan_harvest\Log\Stdout;
use Drupal\dkan_harvest\Log\File;
use Drupal\dkan_harvest\Log\D8Log;
use Drupal\dkan_harvest\Revert;
use Drupal\dkan_harvest\Revert\Dkan8Revert;

class Harvest {

  public $config;

  public $transform = [];

  public $load;

  public $log;

  public function __construct($config) {
    $this->config = $config;
  }

  private function harvestInitValidate($harvest) {
    if (isset($harvest->sourceId) &&
      isset($harvest->transforms) &&
      isset($harvest->runId) &&
      isset($harvest->load) &&
      isset($harvest->source) &&
      isset($harvest->source->type) &&
      isset($harvest->source->uri)) {
      return TRUE;
    }
    else {
      $this->log->write('Error', 'init', 'Harvest settings missing property.');
    }
  }

  function init($harvest) {
    $logClass = "Drupal\\dkan_harvest\\Log\\" . $this->config->log->type;
    $this->log = new $logClass($this->config->log->debug, $harvest->sourceId, $harvest->runId);
    $this->log->write('DEBUG', 'init', 'Initializing harvest');
    if (!$this->harvestInitValidate($harvest)) return FALSE;

    $extractClass = "Drupal\\dkan_harvest\\Extract\\" . $harvest->source->type;
    $this->extract = new $extractClass($this->config, $harvest, $this->log);

    $this->transforms = $this->initializeTransforms($harvest->transforms);

    $loadClass = "Drupal\\dkan_harvest\\Load\\" . $harvest->load->type;
    $this->load = new $loadClass($this->log, $harvest->load, $harvest->sourceId, $harvest->runId);

    $revertClass = "Drupal\\dkan_harvest\\Revert\\Dkan8Revert";
    $this->revert = new $revertClass($this->log, $harvest->sourceId);
    return TRUE;
  }

  function initializeTransforms($transforms) {
    $trans = array();
    foreach ($transforms as $transform) {
      if (is_Object($transform)) {
        $transform = (Array)$transform;
        $name = array_keys($transform)[0];
        $class = "Drupal\\dkan_harvest\\Transform\\" . $name;
        $config = $transform[$name];
        $trans[] = new $class($config, $this->log);
      }
      else {
        $class = "Drupal\\dkan_harvest\\Transform\\" . $transform;
        $trans[] = new $class(NULL, $this->log);
      }
    }
    return $trans;
  }

  function cache() {
    $this->extract->cache();
  }

  function extract() {
    $items = $this->extract->run();
    return $items;
  }

  function transform($items) {
    foreach ($this->transforms as $transform) {
      $transform->run($items);
    }
    return $items;
  }

  function load($items) {
    return $this->load->run($items);
  }

  function revert() {
    $this->revert->run();
  }

}
