<?php

namespace Drupal\dkan_harvest;

use Drupal\dkan_harvest\Extract;
//use Drupal\dkan_harvest\Extract\DataJson;
use Drupal\dkan_harvest\DataJson;
use Drupal\dkan_harvest\Transform;
use Drupal\dkan_harvest\Filter;
use Drupal\dkan_harvest\Override;
use Drupal\dkan_harvest\Load;
use Drupal\dkan_harvest\Dkan8;

class Harvest {

  public $config;

  public $transform = [];

  public $load;

  public function __construct($config) {
    $this->config = $config;
  }

  function init($harvest) {
    var_dump('Initializing harvest: ' . $harvest->id);
    $extract = "Drupal\\dkan_harvest\\" . $harvest->source->type;
    $this->extract = new $extract($this->config, $harvest);
    $this->transforms = $this->initializeTransforms($harvest->transforms);
    $load = "Drupal\\dkan_harvest\\" . $harvest->load->type;
    $this->load = new $load($this->load);
  }

  function initializeTransforms($transforms) {
    $trans = array();
    foreach ($transforms as $transform) {
      if (is_Object($transform)) {
        $transform = (Array)$transform;
        $name = array_keys($transform)[0];
        $class = "Drupal\\dkan_harvest\\" . $name;
        $config = $transform[$name];
        $trans[] = new $class($config);
      }
      else {
        $class = "Drupal\\dkan_harvest\\" . $transform;
        $trans[] = new $class();
      }
    }
    return $trans;
  }

  function cache() {
    $this->extract->cache();
  }

  function extract() {
    $items = $this->extract->load();
    return $items;
  }

  function transform($items) {
    foreach ($this->transforms as $transform) {
      $transform->run($items);
    }
  }

  function load($items) {
    $this->load->run($items);
  }

}
