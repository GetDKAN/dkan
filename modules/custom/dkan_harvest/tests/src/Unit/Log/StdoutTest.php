<?php

namespace Drupal\Tests\dkan_harvest\Unit\Log;

use Drupal\dkan_common\Tests\DkanTestBase;
use Drupal\dkan_harvest\Log\Stdout;

/**
 * Tests Drupal\dkan_harvest\Log\Stdout.
 *
 * @coversDefaultClass Drupal\dkan_harvest\Log\Stdout
 * @group dkan_harvest
 */
class StdoutTest extends DkanTestBase {

  /**
   * Tests write().
   */
  public function testWrite() {
    // Setup.
    $mock = $this->getMockBuilder(Stdout::class)
      ->disableOriginalConstructor()
      ->setMethods(['logEntry'])
      ->getMock();

    $action   = 'foo';
    $level    = 'NOTICE';
    $message  = 'something went foo';
    $logEntry = [
      'message' => $message,
    ];

    // Expect.
    $mock->expects($this->once())
      ->method('logEntry')
      ->with($action, $level, $message)
      ->willReturn($logEntry);

    // Assert.
    // Need to capture output since stdout.
    ob_start();
    $mock->write($level, $action, $message);

    $actual = ob_get_clean();
    $this->assertEquals($logEntry['message'] . "\n", $actual);
  }

}
