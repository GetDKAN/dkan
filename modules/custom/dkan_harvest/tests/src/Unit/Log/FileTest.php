<?php

namespace Drupal\Tests\dkan_harvest\Unit\Log;

use Drupal\dkan_common\Tests\DkanTestBase;
use Drupal\dkan_harvest\Log\File;

/**
 * Tests Drupal\dkan_harvest\Log\File.
 *
 * @coversDefaultClass Drupal\dkan_harvest\Log\File
 * @group dkan_harvest
 */
class FileTest extends DkanTestBase {

  /**
   *
   */
  public function testWrite() {
    // Setup.
    $mock = $this->getMockBuilder(File::class)
      ->disableOriginalConstructor()
      ->setMethods([
        'logEntry',
        'flatten',
        'appendToFile',
      ])
      ->getMock();

    $action   = uniqid('action');
    $level    = uniqid('level');
    $message  = uniqid('message');
    $logEntry = [uniqid('not important')];
    $entry    = uniqid('entry');
    // Expect.
    $mock->expects($this->once())
      ->method('logEntry')
      ->with($action, $level, $message)
      ->willReturn($logEntry);

    $mock->expects($this->once())
      ->method('flatten')
      ->with($logEntry)
      ->willReturn($entry);

    $mock->expects($this->once())
      ->method('appendToFile')
      ->with($entry);

    // Assert.
    $mock->write($level, $action, $message);
  }

  /**
   * Data provider for testFlatten().
   *
   * @return array
   */
  public function dataTestFlatten() {
    $randKey   = uniqid();
    $randValue = uniqid();
    return [
        [['foo' => 'bar'], 'foo: bar' . "\n"],
        [['foo' => 'bar', $randKey => $randValue], "foo: bar, {$randKey}: {$randValue}\n"],
    ];
  }

  /**
   * Tests flatten().
   *
   * @dataProvider dataTestFlatten
   */
  public function testFlatten($entry, $expected) {

    // Setup.
    $mock = $this->getMockBuilder(File::class)
      ->disableOriginalConstructor()
      ->setMethods(NULL)
      ->getMock();

    // Assert.
    $this->assertEquals($expected, $mock->flatten($entry));
  }

  /**
   * Tests appendToFile().
   */
  public function testAppendToFile() {
    $this->markTestSkipped("There's some todo's in SUT.");
  }

}
