<?php

namespace Drupal\Tests\dkan_common\Unit\Mocks;

use Drupal\dkan_common\LoggerTrait;

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
