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
    $data = ['uuid' => 'foo'];
    $this->expectOutputString(json_encode($data, JSON_PRETTY_PRINT));

    $datasetInfo = (new Chain($this))
      ->add(DatasetInfo::class, 'gather', $data);
    $drush = new CommonCommands($datasetInfo->getMock());
    $drush->datasetInfo('foo');
  }

}
