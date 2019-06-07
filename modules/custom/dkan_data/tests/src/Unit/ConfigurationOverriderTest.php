<?php

namespace Drupal\dkan_datastore;

use Drupal\dkan_data\ConfigurationOverrider;
use Drupal\dkan_common\Tests\DkanTestBase;
use Drupal\dkan_schema\SchemaRetriever;

/**
 * Configuration Overrider Test.
 */
class ConfigurationOverriderTest extends DkanTestBase {

  /**
   * Data provider for testOverrider.
   *
   * @return array
   *   Array of arguments.
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
   * Tests loadOverrides.
   *
   * @param array $input
   *   Input.
   * @param array $expected
   *   Expected.
   *
   * @dataProvider dataTestOverrider
   */
  public function testOverrider(array $input, array $expected) {

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
   * Tests getSchema().
   */
  public function testGetSchema() {
    // Setup.
    $mock = $this->getMockBuilder(ConfigurationOverrider::class)
      ->setMethods(NULL)
      ->disableOriginalConstructor()
      ->getMock();

    $mockSchemaRetriever = $this->getMockBuilder(SchemaRetriever::class)
      ->setMethods(['retrieve'])
      ->disableOriginalConstructor()
      ->getMock();
    $this->setActualContainer([
      'dkan_schema.schema_retriever' => $mockSchemaRetriever,
    ]);

    $expected = '{"json":"schema"}';

    // Expect.
    $mockSchemaRetriever->expects($this->once())
      ->method('retrieve')
      ->with('dataset')
      ->willReturn($expected);

    // Assert.
    $actual = $this->invokeProtectedMethod($mock, 'getSchema');
    $this->assertSame($expected, $actual);
  }

  /**
   * Tests various inherited methods that don't (yet) have implementation.
   */
  public function testInheritedMethods() {
    $overrider = new ConfigurationOverrider();
    $this->assertNull($overrider->getCacheSuffix());
    $this->assertNull($overrider->getCacheableMetadata("blah"));
    $this->assertNull($overrider->createConfigObject("blah"));
  }

}
