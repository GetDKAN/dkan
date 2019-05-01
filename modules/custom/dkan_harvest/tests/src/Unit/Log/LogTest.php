<?php

namespace Drupal\Tests\dkan_harvest\Unit\Log;

use Drupal\dkan_common\Tests\DkanTestBase;
use Drupal\dkan_harvest\Log\Log;
use Drupal\Component\Datetime\TimeInterface;

/**
 * Tests Drupal\dkan_harvest\Log\Log.
 *
 * @coversDefaultClass Drupal\dkan_harvest\Log\Log
 * @group dkan_harvest
 */
class LogTest extends DkanTestBase {

  /**
   *
   */
  public function dataTestConstruct() {
    return [
        [NULL, FALSE],
        [0, FALSE],
        [1, TRUE],
        ['foobar', TRUE],
    ];
  }

  /**
   * Tests __construct().
   *
   * @dataProvider dataTestConstruct
   * @param mixed $debug
   * @param mixed $expectedDebug
   */
  public function testConstruct($debug, $expectedDebug) {

    // Setup.
    $mock     = $this->getMockBuilder(Log::class)
      ->disableOriginalConstructor()
      ->getMockforAbstractClass();
    $sourceId = uniqid('sourceId');
    $runId    = uniqid('runId');
    // Assert.
    $mock->__construct($debug, $sourceId, $runId);
    $this->assertEquals($expectedDebug, $this->readAttribute($mock, 'debug'));
    $this->assertEquals($sourceId, $this->readAttribute($mock, 'sourceId'));
    $this->assertEquals($runId, $this->readAttribute($mock, 'runId'));
  }

  /**
   * Tests logEntry().
   */
  public function testLogEntry() {
    // Setup.
    $mock = $this->getMockBuilder(Log::class)
      ->disableOriginalConstructor()
      ->getMockforAbstractClass();

    $mockTime = $this->getMockBuilder(TimeInterface::class)
      ->setMethods(['getCurrentTime'])
      ->getMockForAbstractClass();

    $this->setActualContainer([
      'datetime.time' => $mockTime,
    ]);

    $sourceId    = 'foo';
    $runId       = 'bar';
    $currentTime = 123456;
    $action      = 'foo';
    $level       = 'NOTICE';
    $message     = 'something went foo';

    $this->writeProtectedProperty($mock, 'sourceId', $sourceId);
    $this->writeProtectedProperty($mock, 'runId', $runId);

    $expected = [
      'source_id' => $sourceId,
      'run_id'    => $runId,
      'action'    => $action,
      'level'     => $level,
      'message'   => $message,
      'timestamp' => $currentTime,
    ];

    // Expect.
    $mockTime->expects($this->once())
      ->method('getCurrentTime')
      ->willReturn($currentTime);

    // Assert.
    $actual = $mock->logEntry($level, $action, $message);
    $this->assertEquals($expected, $actual);
  }

}
