<?php

namespace Drupal\Tests\dkan_harvest\Unit\Transform;

use Drupal\dkan_common\Tests\DkanTestBase;
use Drupal\dkan_harvest\Transform\DrupalModuleHook;

/**
 * @group dkan
 */
class DrupalModuleHookTest extends DkanTestBase {

  /**
   * Data provider for testRun.
   *
   * @return array
   */
  public function dataTestRun() {

    $originalDataset = (object) ['title' => "Hello"];

    return [
      [$originalDataset],
    ];

  }

  /**
   * Test the ResourceImporter::run method.
   *
   * @dataProvider dataTestRun
   *
   * @param object $datasets
   * @param object $modifiedDataset
   * @param string $expected
   */
  public function testRun($dataset) {
    $transform = $this->getMockBuilder(DrupalModuleHook::class)
      ->setMethods(['hook'])
      ->setConstructorArgs([(object ) []])
      ->getMock();

    $transform->method("hook")->willReturn($dataset);

    $new_dataset = $transform->run($dataset);

    $this->assertEquals(json_encode($dataset), json_encode($new_dataset));
  }

}
