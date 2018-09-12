<?php

namespace Drupal\dkan_harvest;

class Filter extends Transform {

  function run(&$items) {
    parent::run($items);
    $this->log->write('DEBUG', 'Filter', 'Running transform from Filter');
  }
}
