<?php

namespace Drupal\dkan_harvest;

class Override extends Transform {

  function __construct($config = NULL) {
    $this->config = $config;
  }

  function run() {
    parent::run($items);
    var_dump("run transforming from Override ...");
  }
}
