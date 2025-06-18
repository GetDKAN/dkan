<?php

declare(strict_types=1);

namespace Drupal\Tests\metastore_search\Unit\Plugin\search_api\processor;

use Drupal\KernelTests\KernelTestBase;
use Drupal\metastore_search\Plugin\search_api\processor\DkanDatasetAddNid;
use Drupal\search_api\Item\ItemInterface;

/**
 * @covers \Drupal\metastore_search\Plugin\search_api\processor\DkanDatasetAddNid
 * @coversDefaultClass \Drupal\metastore_search\Plugin\search_api\processor\DkanDatasetAddNid
 *
 * @group metastore_search
 * @group kernel
 */
class DkanDatasetAddNidTest extends KernelTestBase {

  /**
   * {@inheritDoc}
   */
  protected static $modules = [
    'common',
    'metastore',
    'metastore_search',
    'search_api',
  ];

  /**
   * @covers ::addFieldValues
   */
  public function testAddFieldValues() {
    /** @var \Drupal\search_api\Processor\ProcessorPluginManager $manager */
    $manager = $this->container->get('plugin.manager.search_api.processor');
    /** @var \Drupal\metastore_search\Plugin\search_api\processor\DkanDatasetAddNid $processor */
    $processor = $manager->createInstance('dkan_dataset_add_nid');
    $this->assertInstanceOf(DkanDatasetAddNid::class, $processor);

    // Create an index item with an ID that shouldn't be processed by
    // DkanDatasetAddNid.
    $item = $this->getMockBuilder(ItemInterface::class)
      ->onlyMethods(['getId', 'getFields'])
      ->getMockForAbstractClass();
    // ::getId() will return an unknown ID.
    $item->expects($this->any())
      ->method('getId')
      ->willReturn('doNotProcess/this_id');
    // ::getFields should never be called because we've rejected the ID.
    $item->expects($this->never())
      ->method('getFields');

    $processor->addFieldValues($item);
  }

}
