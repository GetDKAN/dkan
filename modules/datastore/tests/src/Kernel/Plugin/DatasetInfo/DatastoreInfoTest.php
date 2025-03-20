<?php

declare(strict_types=1);

namespace Drupal\Tests\datastore\Kernel\Plugin\DatasetInfo;

use Drupal\common\DatasetInfo;
use Drupal\Tests\common\Kernel\DatasetInfoTest;

/**
 * Tests the DatastoreInfo plugin for DatasetInfo.
 *
 * @group dkan
 * @group datastore
 * @group kernel
 */
class DatastoreInfoTest extends DatasetInfoTest {

  /**
   * Re-run DatasetInfo test with datastore enabled, ensure plugin is working.
   */
  public function testDatasetInfo() {
    $this->enableModules(['datastore']);
    $datasetInfo = new DatasetInfo($this->container->get('plugin.manager.dataset_info'));
    $datasetInfo->setStorage($this->container->get('dkan.metastore.storage'));
    $datasetInfo->setResourceMapper($this->container->get('dkan.metastore.resource_mapper'));

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

    $this->assertEquals('waiting', $info['latest_revision']['distributions'][0]['fetcher_status']);
    $this->assertEquals(0, $info['latest_revision']['distributions'][0]['fetcher_percent_done']);
    $this->assertEquals('not found', $info['latest_revision']['distributions'][0]['file_path']);
    $this->assertEquals(0, $info['latest_revision']['distributions'][0]['importer_percent_done']);
    $this->assertEquals('waiting', $info['latest_revision']['distributions'][0]['importer_status']);
    $this->assertEquals('', $info['latest_revision']['distributions'][0]['importer_error']);
    $this->assertEquals(NULL, $info['latest_revision']['distributions'][0]['table_name']);

    // Test for edge case where a revision does not have "distributions" key.
    $metadata2 = $metastore->getValidMetadataFactory()->get(json_encode($this->getDataset('bar')), 'dataset');
    $metadata2->remove("$", "distribution");
    $metastore->post('dataset', $metadata2);
    $info = $datasetInfo->gather('bar');
    $this->assertEquals(['Not found'], $info['latest_revision']['distributions']);
  }

}
