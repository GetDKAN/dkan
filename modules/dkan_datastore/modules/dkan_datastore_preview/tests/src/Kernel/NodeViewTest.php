<?php

namespace Drupal\Tests\dkan_datastore_preview\Kernel;

use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Render\Element;
use Drupal\dkan_datastore_preview\Hook\DataPreviewHooks;
use Drupal\KernelTests\KernelTestBase;
use Drupal\node\NodeInterface;

/**
 * Tests the node view integration on real dataset nodes.
 *
 * @covers \Drupal\dkan_datastore_preview\Hook\DataPreviewHooks
 *
 * @group dkan
 * @group dkan_datastore_preview
 * @group kernel
 */
class NodeViewTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'node',
    'user',
    'field',
    'filter',
    'text',
    'content_moderation',
    'workflows',
    'dkan_common',
    'dkan_metastore',
    'dkan_datastore',
    'dkan_datastore_preview',
    'dkan',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installConfig('system');
    $this->installConfig('node');
    $this->installConfig('field');
    $this->installConfig('dkan_common');
    $this->installConfig('dkan_metastore');
    $this->installEntitySchema('node');
    $this->installSchema('node', ['node_access']);
    $this->installEntitySchema('content_moderation_state');
    $this->installEntitySchema('user');
    $this->installEntitySchema('resource_mapping');

    // hook_install() ran before the display config existed; enable the
    // extra field the way the install hook does on a real site.
    EntityViewDisplay::load('node.data.default')
      ->setComponent(DataPreviewHooks::EXTRA_FIELD, ['weight' => 100])
      ->save();
  }

  /**
   * Post a dataset with one CSV and one PDF distribution; return its node.
   *
   * The mediaType is set explicitly because the referencer's URL-based mime
   * guessing has no network access in kernel tests.
   */
  protected function createDataset(string $identifier): NodeInterface {
    /** @var \Drupal\dkan_metastore\MetastoreService $metastore */
    $metastore = $this->container->get('dkan.metastore.service');
    $metadata = $metastore->getValidMetadataFactory()->get(json_encode([
      'title' => 'Preview Test Dataset',
      'identifier' => $identifier,
      'keyword' => ['test'],
      'description' => 'Test Description',
      'modified' => '2020-01-01',
      'accessLevel' => 'public',
      'distribution' => [
        [
          'title' => 'Tabular',
          'downloadURL' => 'http://example.com/data.csv',
          'mediaType' => 'text/csv',
        ],
        [
          'title' => 'Document',
          'downloadURL' => 'http://example.com/doc.pdf',
          'mediaType' => 'application/pdf',
        ],
      ],
    ]), 'dataset');
    $metastore->post('dataset', $metadata);

    $nodes = $this->container->get('entity_type.manager')->getStorage('node')
      ->loadByProperties(['uuid' => $identifier]);
    return reset($nodes);
  }

  /**
   * Run the node view build (which invokes hook_node_view) for a node.
   */
  protected function buildNodeView(NodeInterface $node): array {
    $viewBuilder = $this->container->get('entity_type.manager')->getViewBuilder('node');
    $build = $viewBuilder->view($node, 'full');
    return $viewBuilder->build($build);
  }

  /**
   * A dataset gets one preview per importable distribution with cache tags.
   */
  public function testDatasetPreviews(): void {
    $node = $this->createDataset('preview-test');
    $build = $this->buildNodeView($node);

    $this->assertArrayHasKey(DataPreviewHooks::EXTRA_FIELD, $build);
    $previews = $build[DataPreviewHooks::EXTRA_FIELD];
    // The display weight is stamped onto the extra field by the view builder.
    $this->assertSame(100, $previews['#weight']);
    // The PDF distribution is skipped, leaving one preview child.
    $this->assertSame([0], Element::children($previews));

    $element = $previews[0]['table'];
    $this->assertSame('dkan_datastore_preview', $element['#type']);
    $this->assertStringStartsWith(md5('http://example.com/data.csv') . '__', $element['#resource_id']);
    $this->assertSame(0, $element['#pager_element']);
    $this->assertSame('dp0_', $element['#query_prefix']);
    $this->assertSame('Preview: data.csv', (string) $element['#caption']);

    // Dataset and distribution node tags are both attached.
    $tags = $previews[0]['#cache']['tags'];
    $this->assertContains('node:' . $node->id(), $tags);
    $info = $this->container->get('dkan.common.dataset_info')->gather($node->uuid());
    $distributionUuid = $info['latest_revision']['distributions'][0]['distribution_uuid'];
    $distributionNodes = $this->container->get('entity_type.manager')->getStorage('node')
      ->loadByProperties(['uuid' => $distributionUuid]);
    $this->assertContains('node:' . reset($distributionNodes)->id(), $tags);
  }

  /**
   * A disabled extra field produces no preview.
   */
  public function testDisabledComponent(): void {
    EntityViewDisplay::load('node.data.default')
      ->removeComponent(DataPreviewHooks::EXTRA_FIELD)
      ->save();
    $node = $this->createDataset('preview-disabled');
    $build = $this->buildNodeView($node);
    $this->assertArrayNotHasKey(DataPreviewHooks::EXTRA_FIELD, $build);
  }

  /**
   * Non-dataset data nodes (e.g. distributions) get no preview.
   */
  public function testNonDatasetNode(): void {
    $this->createDataset('preview-nondataset');
    $nodes = $this->container->get('entity_type.manager')->getStorage('node')
      ->loadByProperties(['field_data_type' => 'distribution']);
    $this->assertNotEmpty($nodes);
    $build = $this->buildNodeView(reset($nodes));
    $this->assertArrayNotHasKey(DataPreviewHooks::EXTRA_FIELD, $build);
  }

}
