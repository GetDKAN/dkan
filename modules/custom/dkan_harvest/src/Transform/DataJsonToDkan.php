<?php

namespace Drupal\dkan_harvest;

class DataJsonToDkan extends Transform {

  function __construct($config = NULL) {
    $this->config = $config;
  }

  function run($items) {
    parent::run($items);
  }
}
