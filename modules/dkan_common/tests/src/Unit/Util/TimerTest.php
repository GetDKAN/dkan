<?php

namespace Drupal\Tests\dkan_common\Unit\Util;

use Drupal\dkan_common\Util\Timer;
use PHPUnit\Framework\TestCase;

/**
 */
class TimerTest extends TestCase {

  /**
   *
   */
  public function test() {
    $timer = new Timer();
    $timer->start("hello");
    sleep(1);
    $timer->end("hello");
    $report = "{$timer}";
    $this->assertTrue(substr_count($report, "hello AVG: 1") > 0);
  }

}
