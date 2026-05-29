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
   * Test getOptions and other plugin basics.
   */
  public function testGetOptions() {
    $config = ['schema' => 'theme'];
    $plugin_manager = \Drupal::service('plugin.manager.json_form_option_source');
    $plugin = $plugin_manager->createInstance('metastoreSchema', $config);
    $this->assertInstanceOf(MetastoreSchema::class, $plugin);
    $options = $plugin->getOptions($config);
    $this->assertEquals('node', $plugin->getTargetType($config));

    // Verify that the options match the terms we created.
    $expected_options = [
      'Theme 1' => 'Theme 1',
      'Theme 2' => 'Theme 2',
    ];
    $this->assertEquals($expected_options, $options);

    // Test with invalid config.
    $this->expectException(\InvalidArgumentException::class);
    $plugin->getOptions(['invalid' => 'config']);
  }

  public static function invalidConfigProvider() {
    return [
      'missing schema' => [
        [],
        \InvalidArgumentException::class,
      ],
      'invalid titleProperty type' => [
        ['schema' => 'theme', 'titleProperty' => 123],
        \InvalidArgumentException::class,
      ],
      'invalid returnValue type' => [
        ['schema' => 'theme', 'returnValue' => (object) ['key' => 'value']],
        \InvalidArgumentException::class,
      ],
    ];
  }

  /**
   * Test validateConfig method against various configs.
   *
   * @dataProvider invalidConfigProvider
   */
  public function testValidateConfig($schema, $expected_exception = NULL) {
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
    $this->expectException($expected_exception);
    $plugin->validateConfig($schema);
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
