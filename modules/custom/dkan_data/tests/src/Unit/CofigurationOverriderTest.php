<?php

namespace Drupal\dkan_datastore;

use Drupal\dkan_data\ConfigurationOverrider;
use PHPUnit\Framework\TestCase;

/**
 * Configuration Overrider Test.
 */
class ConfigurationOverriderTest extends TestCase {

  /**
   *
   */
  public function dataTestOverrider() {
    $return = [
      "core.entity_form_display.node.data.default" =>
        [
          'content' =>
            [
              'field_json_metadata' =>
                [
                  'settings' =>
                    [
                      'json_form' => '{"type": "object"}',
                    ],
                ],
            ],
        ],
    ];

    return [
      [['core.entity_form_display.node.data.default'], $return],
      [[], []],
    ];
  }

  /**
   * @dataProvider dataTestOverrider
   */
  public function testOverrider($input, $expected) {

    $overrider = $this->getMockBuilder(ConfigurationOverrider::class)
      ->setMethods([
        'getSchema',
      ])
      ->getMock();

    if (!empty($input)) {
      $overrider->expects($this->once())
        ->method("getSchema")
        ->willReturn('{"type": "object"}');
    }

    $this->assertEquals($expected, $overrider->loadOverrides($input));
  }

  /**
   *
   */
  public function testInheritedMethods() {
    $overrider = new ConfigurationOverrider();
    $this->assertNull($overrider->getCacheSuffix());
    $this->assertNull($overrider->getCacheableMetadata("blah"));
    $this->assertNull($overrider->createConfigObject("blah"));
  }

}
