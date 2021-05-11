<?php

namespace Drupal\Tests\metastore\Functional;

use Drupal\Core\Queue\QueueFactory;
use Drupal\Tests\common\Traits\CleanUp;
use Drupal\Tests\common\Traits\GetDataTrait;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Class OrphanCheckerTest
 *
 * @package Drupal\Tests\metastore\Functional
 * @group metastore
 */
class OrphanCheckerTest extends ExistingSiteBase {
  use GetDataTrait;
  use CleanUp;

  public function setUp() {
    parent::setUp();
    $this->removeHarvests();
    $this->removeAllNodes();
    $this->removeAllMappedFiles();
    $this->removeAllFileFetchingJobs();
    $this->flushQueues();
    $this->removeFiles();
    $this->removeDatastoreTables();
  }

  public function test() {
    /* @var $service \Drupal\metastore\Service */
    $service = \Drupal::service('dkan.metastore.service');
    $service->post('dataset', $this->getDataset(123, 'Test #1', ['district_centerpoints_small.csv']));
    $this->runQueues(['datastore_import']);
    $service->delete('dataset', 123);
    $success = $this->runQueues(['orphan_reference_processor']);
    $this->assertNull($success);
  }

  private function runQueues(array $relevantQueues = []) {
    /** @var \Drupal\Core\Queue\QueueWorkerManager $queueWorkerManager */
    $queueWorkerManager = \Drupal::service('plugin.manager.queue_worker');
    foreach ($relevantQueues as $queueName) {
      $worker = $queueWorkerManager->createInstance($queueName);
      $queue = $this->getQueueService()->get($queueName);
      while ($item = $queue->claimItem()) {
        $worker->processItem($item->data);
        $queue->deleteItem($item);
      }
    }
  }

  private function getQueueService() : QueueFactory {
    return \Drupal::service('queue');
  }
}
