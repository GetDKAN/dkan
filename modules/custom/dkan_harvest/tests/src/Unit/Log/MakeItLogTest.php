<?php

namespace Drupal\Tests\dkan_harvest\Unit\Log;

use Drupal\dkan_harvest\Log\MakeItLog;
use Drupal\dkan_common\Tests\DkanTestBase;
use Drupal\dkan_harvest\Log\Log;

/**
 * Tests Drupal\dkan_harvest\Log\MakeItLog.
 *
 * @coversDefaultClass Drupal\dkan_harvest\Log\MakeItLog
 * @group dkan_harvest
 */
class MakeItLogTest extends DkanTestBase {

  /**
   *
   */
  public function testSetLogger() {
    // Setup.
    $mock = $this->getMockBuilder(MakeItLog::class)
      ->setMethods(NULL)
      ->getMockForTrait();

    // This bit has been purposefully left to fail.
    $logger = uniqid('need to refactor source. no type hints');

    // Assert.
    $mock->setLogger($logger);
    $this->assertEquals($logger, $this->readAttribute($mock, 'logger'));

  }

  /**
   * Test log().
   */
  public function testLog() {

    // Setup.
    $mock = $this->getMockBuilder(MakeItLog::class)
      ->setMethods(NULL)
      ->getMockForTrait();

    $mockLogger = $this->getMockBuilder(Log::class)
      ->setMethods(['write'])
      ->disableOriginalConstructor()
      ->getMockForAbstractClass();
    $this->writeProtectedProperty($mock, 'logger', $mockLogger);

    $level = 'foo';
    $action = 'bar';
    $message = 'foobar';
    // Expect.
    $mockLogger->expects($this->once())
      ->method('write')
      ->with($level, $action, $message);

    // Assert.
    $this->invokeProtectedMethod($mock, 'log', $level, $action, $message);
  }

}
