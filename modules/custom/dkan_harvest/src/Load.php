<?php

namespace Drupal\dkan_harvest;

abstract class Load {

  protected  $config;
  protected $log;

  function __construct($config = NULL, $log) {
    $this->config = $config;
    $this->log = $log;
  }

  function run($items) {
    var_dump("run Loading from ...");
  }

}
