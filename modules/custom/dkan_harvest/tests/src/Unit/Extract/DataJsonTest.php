<?php

namespace Drupal\Tests\dkan_harvest\Unit\Extract;

use Drupal\dkan_common\Tests\DkanTestBase;

/**
 * Tests Drupal\dkan_harvest\Extract\DataJson.
 *
 * @coversDefaultClass Drupal\dkan_harvest\Extract\DataJson
 * @group dkan_harvest
 */
class DataJsonTest extends DkanTestBase {

  /**
   * Tests run().
   */
  public function testRun() {

    $this->markTestSkipped("run() method doesn't seem fully implemented.");

    // $mock = $this->getMockBuilder(DataJson::class)
    //            ->disableOriginalConstructor()
    //            ->setMethods([
    //                'log',
    //                'cache',
    //            ])
    //            ->getMock();
    //
    //        $mockHashStorage = $this->getMockBuilder(Storage::class)
    //            ->setMethods([
    //                'retrieveAll',
    //            ])
    //            ->getMockForAbstractClass();
  }

  /**
   * Tests cache().
   */
  public function testCache() {
    $this->markTestSkipped("The method parent::writeToFile() is currently not implemented.");
  }

}
