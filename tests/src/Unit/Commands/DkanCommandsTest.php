<?php

namespace Drupal\Tests\dkan\Unit\Commands;

use Drupal\dkan\Commands\DkanCommands;
use Drupal\dkan\DatasetInfo;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Drupal\dkan\Commands\CommonCommands
 * @coversDefaultClass \Drupal\dkan\Commands\CommonCommands
 */
class DkanCommandsTest extends TestCase {

  /**
   * @covers ::datasetInfo
   */
  public function testDatasetInfo() {

    $datasetInfo = $this->getMockBuilder(DatasetInfo::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['gather'])
      ->getMock();
    $datasetInfo->expects($this->once())
      ->method('gather')
      ->willReturn(['uuid' => 'foo']);

    $drush = new DkanCommands($datasetInfo);
    $result = $drush->datasetInfo('foo');
    $expected = "{\n    \"uuid\": \"foo\"\n}";

    $this->assertEquals($expected, $result);
  }

}
