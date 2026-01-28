<?php


declare(strict_types=1);

namespace Drupal\Tests\metastore\Kernel\Service;

use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\common\Traits\QueueRunnerTrait;

/**
 * @group dkan
 * @group metastore
 * @group kernel
 *
 * @covers \Drupal\metastore\Service\OrphanNodeProcessor
 * @coversDefaultClass \Drupal\metastore\Service\OrphanNodeProcessor
 */
class OrphanNodeProcessorTest extends KernelTestBase {
  use QueueRunnerTrait;

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
      "publisher" =>
        [
          "@type" => "org:Organization",
          "name" => "Test Org",
        ],
      "theme" =>
        [
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

    $config = $this->config('metastore.settings');
    $config->set('orphan.delete', TRUE);
    $config->save();
  }

  /**
   * Make sure that deleteOutdatedOrphans() removes all orphaned nodes.
   */
  public function testOrphanNodeDeletion() {
    /**
     * @var \Drupal\metastore\MetastoreService $metastore
     */
    $metastore = $this->container->get('dkan.metastore.service');
    $metadata = $metastore->getValidMetadataFactory()->get(json_encode(self::DATASET_DATA), 'dataset');
    $metastore->post('dataset', $metadata);
    $this->assertEquals($this->getRelatedItemCount() + 1, $this->getDataNodeCount());

    $metastore->delete('dataset', '123');
    $this->assertEquals($this->getRelatedItemCount(), $this->getDataNodeCount());

    $this->runQueues(['orphan_reference_processor']);
    /** @var \Drupal\metastore\Service\OrphanNodeProcessor $processor */
    $processor = $this->container->get('dkan.metastore.orphan_node_processor');
    $deleted_nids = $processor->deleteOutdatedOrphans();
    $this->assertEquals($this->getRelatedItemCount(), count($deleted_nids));
    $this->assertEquals(0, $this->getDataNodeCount());
  }

  protected function getRelatedItemCount() {
    // There can only be one publisher and it's stored as an array
    $publisher = count(self::DATASET_DATA['publisher']) ? 1 : 0;
    return count(self::DATASET_DATA['keyword'])
      + count(self::DATASET_DATA['distribution'])
      + $publisher
      + count(self::DATASET_DATA['theme']);
  }

  protected function getDataNodeCount() {
    return $this->container->get('entity_type.manager')->getStorage('node')
      ->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', 'data')
      ->count()
      ->execute();
  }

}
