<?php

namespace Drupal\Tests\dkan_metastore\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\dkan_common\Traits\GetLocalDataTrait;
use Drupal\Tests\dkan_common\Traits\QueueRunnerTrait;
use Drupal\Tests\dkan_common\Traits\DistributionReferenceModeTrait;
use Drupal\Tests\dkan_metastore\Unit\MetastoreServiceTest;
use RootedData\RootedJsonData;

/**
 * @group dkan
 * @group metastore
 * @group functional
 * @group btb
 * @group functional1
 */
class OrphanCheckerTest extends BrowserTestBase {
  use GetLocalDataTrait;
  use QueueRunnerTrait;
  use DistributionReferenceModeTrait;

  protected static $modules = [
    'dkan_datastore',
    'dkan_metastore',
    'node',
  ];

  protected $defaultTheme = 'stark';

  /**
   * Test orphan handling when distribution is removed from dataset.
   */
  public function testOrphanedResourceCleanup() {
    $validMetadataFactory = MetastoreServiceTest::getValidMetadataFactory($this);
    /** @var \Drupal\dkan_metastore\MetastoreService $service */
    $service = $this->container->get('dkan.metastore.service');
    $connection = $this->container->get('database');

    // Create and post a dataset with a valid distribution/downloadURL.
    $dataset_id = '123';
    $dataset_data = $validMetadataFactory->get(
      $this->getDataset($dataset_id, 'Test Dataset', ['district_centerpoints_small.csv']),
      'dataset'
    );
    $service->post('dataset', $dataset_data);

    // Run initial queues to localize and import the resource.
    $this->runQueues(['localize_import', 'datastore_import']);

    // Get dataset info to find the datastore table name and resource ID.
    $datasetInfo = $this->container->get('dkan.common.dataset_info');
    $metadata = $datasetInfo->gather($dataset_id);
    $distribution_info = $metadata['latest_revision']['distributions'][0];
    $table_name = $distribution_info['table_name'];
    $resource_id = $distribution_info['resource_id'];

    // Verify the datastore table exists.
    $this->assertTrue(
      $connection->schema()->tableExists($table_name),
      "Datastore table $table_name should exist after initial import."
    );

    // Now make the distribution orphaned by removing the downloadURL
    // (making it non-viable for import).
    $full_dataset_json = $service->get('dataset', $dataset_id);
    $full_dataset = json_decode($full_dataset_json);
    // Remove the downloadURL to invalidate the distribution
    $full_dataset->distribution[0]->downloadURL = 'http://example.com/nothing.tar';
    $service->patch('dataset', $dataset_id, json_encode($full_dataset));

    // Run the orphan reference processor and resource purger queues.
    $this->runQueues([
      'orphan_reference_processor',
      'resource_purger',
      'orphan_resource_remover',
    ]);

    // Verify the datastore table has been dropped.
    $this->assertFalse(
      $connection->schema()->tableExists($table_name),
      "Datastore table $table_name should not exist after orphan cleanup."
    );

    // Verify the resource is no longer in the resource mapper.
    // Query for the latest version of the resource (without specifying version,
    // which avoids the timestamp stale-version issue).
    $resource_mapper = $this->container->get('dkan.metastore.resource_mapper');
    $resource = $resource_mapper->get($resource_id, 'source');
    $this->assertNull(
      $resource,
      "Resource $resource_id should no longer exist in the resource mapper after orphan cleanup."
    );
  }


  /**
   * Legacy test: basic orphan reference processor execution.
   */
  public function testOrphanReferenceProcessorExecution() {
    $validMetadataFactory = MetastoreServiceTest::getValidMetadataFactory($this);
    /** @var \Drupal\dkan_metastore\MetastoreService $service */
    $service = $this->container->get('dkan.metastore.service');

    $dataset = $validMetadataFactory->get($this->getDataset(123, 'Test #1', ['district_centerpoints_small.csv']), 'dataset');
    $service->post('dataset', $dataset);
    $dataset2 = $validMetadataFactory->get($this->getDataset(456, 'Test #2', ['district_centerpoints_small.csv']), 'dataset');
    $service->post('dataset', $dataset2);
    $this->runQueues(['datastore_import']);
    $service->delete('dataset', 123);

    // We can run the orphan reference processor queue without throwing an
    // exception.
    $this->assertNull(
      $this->runQueues(['orphan_reference_processor'])
    );
  }

}
