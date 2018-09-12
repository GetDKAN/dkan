<?php

namespace Drupal\dkan_harvest;

class Def extends Transform {

  function run(&$items) {
    parent::run($items);
    $this->log->write('DEBUG', 'Def', 'Running transform from Def');
  }
}
