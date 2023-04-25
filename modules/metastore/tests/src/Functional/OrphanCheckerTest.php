<?php

namespace Drupal\Tests\metastore\Functional;

use Drupal\Core\Queue\QueueFactory;
use Drupal\Tests\common\Traits\CleanUp;
use Drupal\Tests\common\Traits\GetDataTrait;
use Drupal\Tests\metastore\Unit\ServiceTest;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Class OrphanCheckerTest
 *
 * @package Drupal\Tests\metastore\Functional
 * @group metastore
 * @group dataset
 */
class OrphanCheckerTest extends ExistingSiteBase {
  use GetDataTrait;
  use CleanUp;

  /**
   * The ValidMetadataFactory class used for testing.
   *
   * @var \Drupal\metastore\ValidMetadataFactory|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $validMetadataFactory;

  public function setUp(): void {
    parent::setUp();
    $this->validMetadataFactory = ServiceTest::getValidMetadataFactory($this);
  }

  protected function tearDown(): void {
    parent::tearDown();
    $this->removeHarvests();
    $this->removeAllNodes();
    $this->removeAllMappedFiles();
    $this->removeAllFileFetchingJobs();
    $this->flushQueues();
    $this->removeFiles();
    $this->removeDatastoreTables();
  }

  public function test() {
    $id_1 = uniqid();
    $id_2 = uniqid();
    /** @var $service \Drupal\metastore\Service */
    $service = \Drupal::service('dkan.metastore.service');
    $dataset = $this->validMetadataFactory->get($this->getDataset($id_1, 'Test #1', ['district_centerpoints_small.csv']), 'dataset');
    $this->assertNotEmpty(
      $service->post('dataset', $dataset)
    );
    $dataset2 = $this->validMetadataFactory->get($this->getDataset($id_2, 'Test #2', ['district_centerpoints_small.csv']), 'dataset');
    $service->post('dataset', $dataset2);
    $this->runQueues(['datastore_import']);
    $service->delete('dataset', $id_1);
    $this->runQueues(['orphan_reference_processor']);
  }

  private function runQueues(array $relevantQueues = []) {
    /** @var \Drupal\Core\Queue\QueueWorkerManager $queueWorkerManager */
    $queueWorkerManager = \Drupal::service('plugin.manager.queue_worker');
    /** @var QueueFactory $queue_factory */
    $queue_factory = \Drupal::service('queue');
    foreach ($relevantQueues as $queueName) {
      $worker = $queueWorkerManager->createInstance($queueName);
      $queue = $queue_factory->get($queueName);
      while ($item = $queue->claimItem()) {
        $worker->processItem($item->data);
        $queue->deleteItem($item);
      }
    }
  }

}
