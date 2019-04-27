<?php

namespace Drupal\Tests\dkan_harvest\Unit;

use Drupal\dkan_common\Tests\DkanTestBase;
use Harvest\Harvester;

/**
 * Description of HarvesterTest
 */
class HarvesterTest extends DkanTestBase {

  public function testConstruct() {

    $this->markTestSkipped('Need to refactor constructor to use DI');
  }

  /**
   * Tests harvest().
   */
  public function testHarvest() {

    $this->markTestSkipped('Need to refactor constructor to use DI');

//        $mock = $this->getMockBuilder(Harvester::class)
//                ->setMethods([
//                    'extract',
//                    'transform',
//                    'load',
//                ])
  }

}
