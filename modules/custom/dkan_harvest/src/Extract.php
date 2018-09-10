<?php

namespace Drupal\dkan_harvest;

abstract class Extract {

  function __construct($config, $harvest) {
    $this->uri = $harvest->source->uri;
    $this->file = $config->fileLocation;
  }

  function load() {
    var_dump('loading Extract');
    return array();
  }

  function cache() {
    var_dump('loading cache');
  }

}
