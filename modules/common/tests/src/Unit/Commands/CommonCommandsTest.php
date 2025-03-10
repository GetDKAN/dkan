<?php

namespace Drupal\Tests\common\Unit\Commands;

use Drupal\common\Commands\CommonCommands;
use Drupal\common\DatasetInfo;
use Drush\TestTraits\CliTestTrait;
use MockChain\Chain;
use PHPUnit\Framework\TestCase;

/**
 *
 */
class CommonCommandsTest extends TestCase {

  use CliTestTrait;

  /**
   *
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
