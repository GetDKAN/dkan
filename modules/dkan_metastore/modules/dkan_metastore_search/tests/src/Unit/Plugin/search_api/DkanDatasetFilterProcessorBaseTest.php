<?php

namespace Drupal\Tests\metastore_search\Unit\Plugin\search_api;

use Drupal\metastore_search\Plugin\search_api\DkanDatasetFilterProcessorBase;
use Drupal\search_api\Datasource\DatasourceInterface;
use Drupal\search_api\IndexInterface;

use MockChain\Chain;
use PHPUnit\Framework\TestCase;

/**
 * Tests DkanDatasetFilterProcessorBase class.
 *
 * @package Drupal\Tests\metastore_search\Unit\Plugin\search_api
 * @group metastore_search
 */
class DkanDatasetFilterProcessorBaseTest extends TestCase {

  /**
   * Test DkanDatasetFilterProcessorBase::supportsIndex() method.
   */
  public function testSupportsIndex() {
    // Test filter processor supports indexes with dkan datasources.
    $dkan_datasource = (new Chain($this))
      ->add(DatasourceInterface::class, 'getPluginId', 'dkan_dataset')
      ->getMock();
    $index_with_dkan_datasource = (new Chain($this))
      ->add(IndexInterface::class, 'getDatasources', [$dkan_datasource])
      ->getMock();
    $this->assertTrue(DkanDatasetFilterProcessorBase::supportsIndex($index_with_dkan_datasource));

    // Test filter processor doesn't support indexes without dkan datasources.
    $non_dkan_datasource = (new Chain($this))
      ->add(DatasourceInterface::class, 'getPluginId', 'non_dkan_dataset')
      ->getMock();
    $index_without_dkan_datasource = (new Chain($this))
      ->add(IndexInterface::class, 'getDatasources', [$non_dkan_datasource])
      ->getMock();
    $this->assertFalse(DkanDatasetFilterProcessorBase::supportsIndex($index_without_dkan_datasource));
  }

}
