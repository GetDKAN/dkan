<?php

declare(strict_types=1);

namespace Drupal\Tests\common\Kernel;

use Drupal\common\DatasetInfo;
use Drupal\KernelTests\KernelTestBase;

/**
 * @group dkan
 * @group common
 * @group kernel
 */
class DatasetInfoTest extends KernelTestBase {

  public static $modules = [
    'system',
    'node',
    'user',
    'field',
    'filter',
    'text',
    'metastore',
    'common',
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
    $this->installConfig('common');
    $this->installConfig('metastore');
    $this->installEntitySchema('node');
    $this->installSchema('node', ['node_access']);
    $this->installEntitySchema('content_moderation_state');
    $this->installConfig('field');
    $this->installEntitySchema('user');
    $this->installEntitySchema('resource_mapping');
  }

  public function testDatasetInfo() {
    $datasetInfo = new DatasetInfo($this->container->get('plugin.manager.dataset_info'));
    $datasetInfo->setStorage($this->container->get('dkan.metastore.storage'));
    $datasetInfo->setResourceMapper($this->container->get('dkan.metastore.resource_mapper'));
    $info = $datasetInfo->gather('foo');
    // No dataset with that identifier.
    $this->assertEquals(['notice' => 'Not found'], $info);

    $this->config('workflows.workflow.dkan_publishing')
      ->set('type_settings.default_moderation_state', 'draft')
      ->save();

    /**
     * @var \Drupal\metastore\MetastoreService $metastore
     */
    $metastore = $this->container->get('dkan.metastore.service');
    $metadata = $metastore->getValidMetadataFactory()->get(json_encode($this->getDataset('foo')), 'dataset');
    $metastore->post('dataset', $metadata);

    $info = $datasetInfo->gather('foo');
    $this->assertArrayHasKey('latest_revision', $info);
    $this->assertArrayHasKey('uuid', $info['latest_revision']);
    $this->assertCount(2, $info['latest_revision']['distributions']);

    $downloadUrl1 = $metadata->{"$.distribution[0].downloadURL"};
    $this->assertEquals(md5((string) $downloadUrl1), $info['latest_revision']['distributions'][0]['resource_id']);
    $downloadUrl2 = $metadata->{"$.distribution[1].downloadURL"};
    $this->assertEquals(md5((string) $downloadUrl2), $info['latest_revision']['distributions'][1]['resource_id']);

    // Datastore isn't enabled, so file_path shouldn't be set.
    // @see: \Drupal\Tests\datastore\Kernel\DatasetInfoTest
    $this->assertArrayNotHasKey('file_path', $info['latest_revision']['distributions'][0]);

    // Publish the dataset, then patch it to get a published + latest revision.
    $metastore->publish('dataset', 'foo');

    $title = ['title' => 'New Title'];
    $metastore->patch('dataset', 'foo', json_encode($title));

    $info = $datasetInfo->gather('foo');
    $this->assertArrayHasKey('latest_revision', $info);
    $this->assertArrayHasKey('published_revision', $info);

    // Now try a dataset with no distributions.
    $metadata2 = $metastore->getValidMetadataFactory()->get(json_encode($this->getDataset('bar')), 'dataset');
    $metadata2->remove("$", "distribution");
    $metastore->post('dataset', $metadata2);
    $info = $datasetInfo->gather('bar');
    $this->assertEquals(['Not found'], $info['latest_revision']['distributions']);

    // Now try a distribution with no resources.
    $metadata3 = $metastore->getValidMetadataFactory()->get(json_encode($this->getDataset('res')), 'dataset');
    $metadata3->remove("$.distribution[*]", "downloadURL");
    $metastore->post('dataset', $metadata3);
    $info = $datasetInfo->gather('res');
    $this->assertEquals('No resource found', $info["latest_revision"]["distributions"][0][0]);
    $this->assertEquals('No resource found', $info["latest_revision"]["distributions"][1][0]);
  }

  public function testDatasetInfoNoMetastore() {
    $this->disableModules(['metastore']);
    $datasetInfo = new DatasetInfo($this->container->get('plugin.manager.dataset_info'));
    $info = $datasetInfo->gather('foo');
    $this->assertEquals(['notice' => 'The DKAN Metastore module is not enabled.'], $info);
  }

  protected function getDataset(string $identifier): array {
    return [
      'title' => 'Test Dataset',
      'identifier' => $identifier,
      'keyword' => ['test'],
      'description' => 'Test Description',
      'modified' => '2020-01-01',
      'accessLevel' => 'public',
      'distribution' => [
        [
          'title' => 'Test Distribution 1',
          'downloadURL' => 'http://example.com/1.csv',
        ],
        [
          'title' => 'Test Distribution 2',
          'downloadURL' => 'http://example.com/2.csv',
        ],
      ],
    ];
  }

}
