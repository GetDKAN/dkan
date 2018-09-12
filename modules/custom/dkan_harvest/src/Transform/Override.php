<?php

namespace Drupal\dkan_harvest;

class Override extends Transform {

  function run(&$items) {
    parent::run($items);
    $this->log->write('DEBUG', 'Override', 'Running transform from Override');
  }
}
