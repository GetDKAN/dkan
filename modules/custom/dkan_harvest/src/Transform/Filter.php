<?php

namespace Drupal\dkan_harvest;

class Filter extends Transform {

  function __construct($config = NULL) {
    $this->config = $config;
  }

  function run(&$items) {
    parent::run($items);
    var_dump("run transforming from Filter ...");
  }
}
