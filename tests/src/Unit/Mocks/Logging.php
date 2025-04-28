<?php

namespace Drupal\Tests\dkan\Unit\Mocks;

use Drupal\dkan\LoggerTrait;

/**
 *
 */
class Logging {
  use LoggerTrait;

  /**
   *
   */
  public function do() {
    $this->showDebug();
    $this->notice("hello");
    $this->debug('goodbye');
  }

}
