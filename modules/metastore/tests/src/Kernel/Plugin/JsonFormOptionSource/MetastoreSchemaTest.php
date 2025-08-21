<?php

declare(strict_types=1);

namespace Drupal\Tests\metastore\Kernel\Plugin\JsonFormOptionSource;

use Drupal\KernelTests\KernelTestBase;
use Drupal\metastore\MetastoreService;
use Drupal\metastore\Plugin\JsonFormOptionSource\MetastoreSchema;
use MockChain\Chain;
use MockChain\Options;

/**
 * Test coverage for MetastoreSchema plugin.
 *
 * @group metastore
 * @coversDefaultClass \Drupal\metastore\Plugin\JsonFormOptionSource\MetastoreSchema
 */
class MetastoreSchemaTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'common',
    'metastore',
    'workflows',
    'content_moderation',
    'json_form_widget',
    'node',
    'user',
    'system',
    'field',
    'text',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('node');
    // $this->installEntitySchema('user');
    $this->installConfig(['node']);
    $this->installConfig(['workflows']);
    $this->installConfig(['metastore']);

    $metastore = (new Chain($this))
      ->add(MetastoreService::class, 'getAll', (new Options())
        ->add('theme', static::themes())
        ->index(0)
      )
      ->getMock();

    // Replace dkan.metastore.service in the container with the mock.
    $this->container->set('dkan.metastore.service', $metastore);
  }

  /**
   * Test getOptions method.
   */
  public function testGetOptions() {
    $plugin_manager = \Drupal::service('plugin.manager.json_form_option_source');
    $plugin = $plugin_manager->createInstance('metastoreSchema', ['schema' => 'theme']);
    assert($plugin instanceof MetastoreSchema);
    $options = $plugin->getOptions(['schema' => 'theme']);

    // Verify that the options match the terms we created.
    $expected_options = [
      'Theme 1' => 'Theme 1',
      'Theme 2' => 'Theme 2',
    ];
    $this->assertEquals($expected_options, $options);
  }

  /**
   * Test getTargetType method returns 'node'.
   */
  public function testGetTargetType() {
    $plugin_manager = \Drupal::service('plugin.manager.json_form_option_source');
    $plugin = $plugin_manager->createInstance('metastoreSchema', ['schema' => 'theme']);
    $target_type = $plugin->getTargetType(['schema' => 'theme']);

    // Verify that the target type is as expected.
    $expected_target_type = 'node';
    $this->assertEquals($expected_target_type, $target_type);
  }

  /**
   * Test validateConfig method against various configs.
   */
  public function testValidateConfig() {
    $plugin_manager = \Drupal::service('plugin.manager.json_form_option_source');
    $plugin = $plugin_manager->createInstance('metastoreSchema', ['schema' => 'theme']);

    // Test valid config.
    $this->assertTrue($plugin->validateConfig(['schema' => 'theme']));
    $this->assertTrue($plugin->validateConfig([
      'schema' => 'theme',
      'config' => [
        'titleProperty' => 'name',
        'returnValue' => 'id',
      ],
    ]));

    // Test missing schema.
    $this->expectException(\InvalidArgumentException::class);
    $plugin->validateConfig([]);

    // Test invalid titleProperty type.
    $this->expectException(\InvalidArgumentException::class);
    $plugin->validateConfig(['schema' => 'theme', 'titleProperty' => 123]);

    // Test invalid returnValue type.
    $this->expectException(\InvalidArgumentException::class);
    $plugin->validateConfig(['schema' => 'theme', 'returnValue' => (object) ['key' => 'value']]);
  }

  /**
   * Dummy list of themes to mock metastore service.
   */
  public static function themes() {
    return [
      json_encode((object) [
        'identifier' => '111',
        'data' => 'Theme 1',
      ]),
      json_encode((object) [
        'identifier' => '222',
        'data' => 'Theme 2',
      ]),
    ];
  }

}
