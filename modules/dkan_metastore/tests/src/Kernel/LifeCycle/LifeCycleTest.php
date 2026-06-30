<?php

declare(strict_types=1);

namespace Drupal\Tests\dkan_metastore\LifeCycle;

use Drupal\dkan_common\DataResource;
use Drupal\KernelTests\KernelTestBase;
use RootedData\Exception\ValidationException;

/**
 * Some tests for LifeCycle hooks.
 *
 * @group dkan
 * @group dkan_metastore
 * @group kernel
 *
 * @covers \Drupal\dkan_metastore\LifeCycle\LifeCycle
 * @coversDefaultClass \Drupal\dkan_metastore\LifeCycle\LifeCycle
 */
class LifeCycleTest extends KernelTestBase {
  protected const DATASET_DATA = [
    'title' => 'Test Dataset',
    'identifier' => '123',
    'description' => 'Test Description',
    'modified' => '2026-01-01',
    'accessLevel' => 'public',
    'keyword' => ['test'],
    'distribution' => [
      [
        'title' => 'Test Distribution 1',
        'downloadURL' => 'http://example.com/1.csv',
      ],
    ],
    "publisher" => [
      "@type" => "org:Organization",
      "name" => "Test Org",
    ],
    "theme" => [
      "Tag 1",
      "Tag 2",
    ],
  ];


  public static $modules = [
    'system',
    'node',
    'user',
    'field',
    'filter',
    'text',
    'dkan_metastore',
    'dkan_common',
    'dkan',
    'content_moderation',
    'workflows',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installConfig('system');
    $this->installConfig('node');
    $this->installConfig('dkan_common');
    $this->installConfig('dkan_metastore');
    $this->installEntitySchema('node');
    $this->installSchema('node', ['node_access']);
    $this->installEntitySchema('content_moderation_state');
    $this->installConfig('field');
    $this->installEntitySchema('user');
    $this->installEntitySchema('resource_mapping');
  }

  /**
   * Test dataset save, primarily looking at distribution behavior.
   *
   * @param string|null $download_url
   *   The download URL to use for the distribution.
   * @param string $dist_reference
   *   The distribution reference setting to use.
   *
   * @dataProvider testDatasetSaveProvider
   * @covers ::datasetSave
   */
  public function testDatasetSave(?string $download_url, string $dist_reference) {
    // Set the "distribution" property list item to use references or not.
    $property_list = $this->config('dkan_metastore.settings')->get('property_list');
    $property_list['distribution'] = $dist_reference;
    $this->config('dkan_metastore.settings')
      ->set('property_list', $property_list)
      ->save();

    $dataset_data = self::DATASET_DATA;
    $dist_2 = [
      'title' => 'Test Distribution 2',
      'downloadURL' => $download_url,
    ];
    $dataset_data['distribution'][1] = array_filter($dist_2);

    /**
     * @var \Drupal\dkan_metastore\MetastoreService $metastore
     */
    $metastore = $this->container->get('dkan.metastore.service');
    $metadata = $metastore->getValidMetadataFactory()->get(json_encode($dataset_data), 'dataset');
    $identifier = $metastore->post('dataset', $metadata);
    $new_dataset = $metastore->get('dataset', $identifier);

    $this->assertEquals($download_url, $new_dataset->{"$.distribution[1].downloadURL"});
    $this->assertEquals($dataset_data['distribution'][1]['title'], $new_dataset->{"$.distribution[1].title"});

    if ($download_url) {
      // Assert a resource mapping entity was created for the distribution.
      $identifier = md5($download_url);
      $storage = $this->container->get('entity_type.manager')->getStorage('resource_mapping');
      $entities = $storage->loadByProperties([
        'identifier' => $identifier,
      ]);
      $this->assertNotEmpty($entities, 'Resource mapping entity was created successfully.');
    }
    else {
      // Assert no resource mapping entities exist.
      $storage = $this->container->get('entity_type.manager')->getStorage('resource_mapping');
      $ids = $storage->getQuery()
        ->accessCheck(FALSE)
        ->execute();
      $this->assertEquals(1, count($ids), 'Only one resource mapping entity exists.');
    }
  }

  /**
   * Two versions of metastore settings.
   *
   * Setting dkan_metastore.settings.property_list.distribution to "0" means
   * we don't reference distributions. Tests that the pre-reference event
   * subscriber still works in that case.
   */
  public static function testDatasetSaveProvider() {
    $configs = ['distribution', '0'];
    $download_urls = [
      'http://example.com/1.csv',
      'http://example.com/2.csv',
      'http://example.com/2.tar',
      'public://files/local.csv',
      'file:///home/user/data.csv',
      NULL,
    ];
    $data = [];
    foreach ($configs as $config) {
      foreach ($download_urls as $url) {
        $data[] = [$url, $config];
      }
    }
    return $data;
  }

  /**
   * Make sure that distributionLoad properly creates references.
   */
  public function testDistributionLoad() {
    /**
     * @var \Drupal\dkan_metastore\MetastoreService $metastore
     */
    $metastore = $this->container->get('dkan.metastore.service');
    $metadata = $metastore->getValidMetadataFactory()->get(json_encode(self::DATASET_DATA), 'dataset');
    $identifier = $metastore->post('dataset', $metadata);
    $result = $this->container->get('entity_type.manager')
      ->getStorage('node')
      ->loadByProperties(['type' => 'data', 'uuid' => $identifier]);
    $node = reset($result);

    // Get the raw value from the database for field_json_metadata.
    $query = $this->container->get('database')->query(
      'SELECT field_json_metadata_value FROM {node__field_json_metadata} WHERE entity_id = :entity_id',
      [':entity_id' => $node->id()]
    );
    $json_raw = $query->fetchField();
    $dataset_raw = json_decode($json_raw, TRUE);
    $distribution_id = $dataset_raw['distribution'][0];
    $result = $this->container->get('entity_type.manager')
      ->getStorage('node')
      ->loadByProperties(['type' => 'data', 'uuid' => $distribution_id]);
    $distribution_node = reset($result);

    // Get the raw value for the distribution JSON from the DB.
    $query = $this->container->get('database')->query(
      'SELECT field_json_metadata_value FROM {node__field_json_metadata} WHERE entity_id = :entity_id',
      [':entity_id' => $distribution_node->id()]
    );
    $json_raw = $query->fetchField();
    $distribution_raw = json_decode($json_raw, TRUE);
    $download_url_ref = $distribution_raw['data']['downloadURL'];
    $resource_parts = DataResource::parseUniqueIdentifier($download_url_ref);

    // Delete the resource mapping entity.
    $storage = $this->container->get('entity_type.manager')->getStorage('resource_mapping');
    $entities = $storage->loadByProperties([
      'identifier' => $resource_parts['identifier'],
      'version' => $resource_parts['version'],
      'perspective' => $resource_parts['perspective'],
    ]);
    foreach ($entities as $entity) {
      $entity->delete();
    }

    // Avoid reusing the already-loaded distribution entity with a resolved URL.
    $this->container->get('entity_type.manager')->getStorage('node')->resetCache();

    // Re-load the original dataset via the metastore service.
    try {
      $metastore->get('dataset', $identifier);
      $this->fail('Expected a ValidationException to be thrown due to the missing resource mapping.');
    }
    catch (ValidationException $e) {
      $this->assertEquals('JSON Schema validation failed.', $e->getMessage());
    }

    // Enable the unset_download_url_if_empty setting and try again.
    $config = $this->container->get('config.factory')->getEditable('dkan_metastore.settings');
    $config->set('unset_download_url_if_empty', TRUE);
    $config->save();
    $this->container->get('config.factory')->reset('dkan_metastore.settings');
    $this->container->get('entity_type.manager')->getStorage('node')->resetCache();

    $dataset = $metastore->get('dataset', $identifier);
    $this->assertArrayNotHasKey('downloadURL', $dataset->{"$.distribution[0]"});
  }

}
