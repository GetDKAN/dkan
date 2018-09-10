<?php

namespace Drupal\dkan_harvest;

abstract class Load {

  private $config;

  function __construct($config = NULL) {
    $this->config = $config;
  }

  function run($items) {
    var_dump("run Loading from ...");
  }

}
