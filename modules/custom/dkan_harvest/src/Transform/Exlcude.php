<?php

namespace Drupal\dkan_harvest;

class Exlcude extends Transform {

  function run(&$items) {
    parent::run($items);
    $this->log->write('DEBUG', 'Exclude', 'Running transform from Exclude');
  }
}
