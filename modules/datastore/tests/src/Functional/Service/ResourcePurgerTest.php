<?php

namespace Drupal\Tests\datastore\Functional\Service;

use Drupal\Tests\common\Traits\CleanUp;
use Drupal\Tests\common\Traits\GetDataTrait;
use Drupal\Tests\metastore\Unit\MetastoreServiceTest;

use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Test ResourcePurger service.
 *
 * @package Drupal\Tests\datastore\Functional
 * @group datastore
 */
class ResourcePurgerTest extends ExistingSiteBase {
  use GetDataTrait;
  use CleanUp;

  /**
   * DKAN dataset storage service.
   *
   * @var \Drupal\metastore\Storage\NodeData
   */
  protected $datasetStorage;

  /**
   * DKAN datastore service.
   *
   * @var \Drupal\datastore\DatastoreService
   */
  protected $datastore;

  /**
   * DKAN metastore service.
   *
   * @var \Drupal\metastore\MetastoreService
   */
  protected $metastore;

  /**
   * The Drupal Core Queue service.
   *
   * @var \Drupal\Core\Queue\QueueFactory
   */
  protected $queue;


  /**
   * The Drupal Core queue worker manager service.
   *
   * @var \Drupal\Core\Queue\QueueWorkerManager
   */
  protected $queueWorkerManager;

  /**
   * DKAN resource purger service.
   *
   * @var \Drupal\datastore\Service\ResourcePurger
   */
  protected $resourcePurger;

  /**
   * The ValidMetadataFactory class used for testing.
   *
   * @var \Drupal\metastore\ValidMetadataFactory|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $validMetadataFactory;

  public function setUp(): void {
    parent::setUp();

    // Prepare environment.
    $this->removeHarvests();
    $this->removeAllNodes();
    $this->removeAllMappedFiles();
    $this->removeAllFileFetchingJobs();
    $this->flushQueues();
    $this->removeFiles();
    $this->removeDatastoreTables();

    // Initialize services.
    $this->datasetStorage = \Drupal::service('dkan.metastore.storage')->getInstance('dataset');
    $this->datastore = \Drupal::service('dkan.datastore.service');
    $this->metastore = \Drupal::service('dkan.metastore.service');
    $this->queue = \Drupal::service('queue');
    $this->queueWorkerManager = \Drupal::service('plugin.manager.queue_worker');
    $this->resourcePurger = \Drupal::service('dkan.datastore.service.resource_purger');
    $this->validMetadataFactory = MetastoreServiceTest::getValidMetadataFactory($this);
  }

  /**
   * Test deleting a dataset doesn't delete other datasets sharing a resource.
   */
  public function testDatasetsWithSharedResourcesAreNotDeletedPrematurely(): void {
    // Create 2 datasets with the same resource, and change the resource of one.
    $dataset = $this->validMetadataFactory->get($this->getDataset(123, 'Test #1', ['district_centerpoints_small.csv']), 'dataset');
    $this->metastore->post('dataset', $dataset);
    $this->assertNotEmpty($this->datasetStorage->retrieve(123));
    $this->runQueues(['datastore_import']);
    $dataset = $this->validMetadataFactory->get($this->getDataset(123, 'Test #1', ['retirements_0.csv']), 'dataset');
    $this->metastore->patch('dataset', 123, $dataset);
    $this->assertNotEmpty($this->datasetStorage->retrieve(123));

    $dataset2 = $this->validMetadataFactory->get($this->getDataset(456, 'Test #2', ['district_centerpoints_small.csv']), 'dataset');
    $this->metastore->post('dataset', $dataset2);
    $this->assertNotEmpty($this->datasetStorage->retrieve(456));
    $this->runQueues(['datastore_import']);

    // Ensure calling the resource purger on the updated dataset does not delete
    // the previously shared resource.
    $this->resourcePurger->schedule([123], FALSE);
    $resources = $this->getResourcesForDataset(456);
    $resource = reset($resources);
    $this->assertNotEmpty($this->datastore->getStorage($resource->identifier, $resource->version));
  }

  /**
   * Get resources for the dataset belonging to the supplied dataset identifier.
   *
   * @param string $dataset_identifier
   *   A dataset UUID.
   *
   * @return object[]
   *   Resource objects containing a unique "identifier" and "version" pair.
   */
  protected function getResourcesForDataset(string $dataset_identifier): array {
    // Retrieve dataset metastore storage service.
    $metadata = $this->datasetStorage->retrieve($dataset_identifier);
    $distributions = json_decode($metadata)->{'%Ref:distribution'} ?? [];

    $resources = [];
    foreach ($distributions as $distribution) {
      // Retrieve and validate the resource for this distribution before adding
      // it to the resources list.
      $resource = $distribution->data->{'%Ref:downloadURL'}[0] ?? NULL;
      if (isset($resource->data->identifier, $resource->data->version)) {
        $resources[] = $resource->data;
      }
    }

    return $resources;
  }

  /**
   * Process the supplied queue list.
   *
   * @param string[] $relevant_queues
   *   A list of queues to process.
   */
  protected function runQueues(array $relevant_queues = []): void {
    foreach ($relevant_queues as $queue_name) {
      $worker = $this->queueWorkerManager->createInstance($queue_name);
      $queue = $this->queue->get($queue_name);
      while ($item = $queue->claimItem()) {
        $worker->processItem($item->data);
        $queue->deleteItem($item);
      }
    }
  }
}
