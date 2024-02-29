<?php

namespace Drupal\Tests\common\Unit\Mocks;

use Drupal\common\LoggerTrait;

/**
 *group unit
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
