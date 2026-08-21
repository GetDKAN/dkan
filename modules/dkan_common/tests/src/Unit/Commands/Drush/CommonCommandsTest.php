<?php

namespace Drupal\Tests\dkan_common\Unit\Commands;

use Drupal\dkan_common\Drush\Commands\CommonCommands;
use Drupal\dkan_common\DatasetInfo;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Drupal\dkan_common\Drush\Commands\CommonCommands
 * @coversDefaultClass \Drupal\dkan_common\Drush\Commands\CommonCommands
 */
class CommonCommandsTest extends TestCase {

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

    $drush = new CommonCommands($datasetInfo);
    $result = $drush->datasetInfo('foo');
    $expected = "{\n    \"uuid\": \"foo\"\n}";

    $this->assertEquals($expected, $result);
  }

}
