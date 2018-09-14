<?php

namespace Drupal\dkan_harvest\Transform;

use Drupal\dkan_harvest\Transform;

class Filter extends Transform {

  function run(&$items) {
    parent::run($items);
    $this->log->write('DEBUG', 'Filter', 'Running transform from Filter');
    // TODO: Filter.
    return $items;
  }
}
