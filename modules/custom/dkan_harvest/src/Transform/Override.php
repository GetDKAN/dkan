<?php

namespace Drupal\dkan_harvest\Transform;

use Drupal\dkan_harvest\Transform;

class Override extends Transform {

  function run(&$items) {
    parent::run($items);
    $this->log->write('DEBUG', 'Override', 'Running transform from Override');
    // TODO: Override.
    return $items;
  }
}
