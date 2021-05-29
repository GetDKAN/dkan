<?php

namespace Drupal\Tests\common\Unit\Commands;

use Drupal\common\Commands\CommonCommands;
use Drupal\common\DatasetInfo;
use MockChain\Chain;
use PHPUnit\Framework\TestCase;

/**
 *
 */
class CommonCommandsTest extends TestCase {

  /**
   *
   */
  public function testDatasetInfo() {

    $datasetInfo = (new Chain($this))
      ->add(DatasetInfo::class, 'gather', ['uuid' => 'foo']);

    $drush = new CommonCommands($datasetInfo->getMock());
    $result = $drush->datasetInfo('foo');

    $expected = json_encode(['uuid' => 'foo'], JSON_PRETTY_PRINT);

    $this->expectOutputString($expected, $result);
  }

}
