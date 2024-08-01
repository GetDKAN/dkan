<?php

namespace Drupal\Tests\metastore\Functional;

use Drupal\Core\Queue\QueueFactory;
use Drupal\Tests\common\Traits\CleanUp;
use Drupal\Tests\common\Traits\GetDataTrait;
use Drupal\Tests\common\Traits\QueueRunnerTrait;
use Drupal\Tests\metastore\Unit\MetastoreServiceTest;
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
  use QueueRunnerTrait;

  /**
   * The ValidMetadataFactory class used for testing.
   *
   * @var \Drupal\metastore\ValidMetadataFactory|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $validMetadataFactory;

  public function setUp(): void {
    parent::setUp();
    $this->removeHarvests();
    $this->removeAllNodes();
    $this->removeAllMappedFiles();
    $this->removeAllFileFetchingJobs();
    $this->flushQueues();
    $this->removeFiles();
    $this->removeDatastoreTables();
    $this->validMetadataFactory = MetastoreServiceTest::getValidMetadataFactory($this);
  }

  public function test() {
    /** @var $service \Drupal\metastore\MetastoreService */
    $service = \Drupal::service('dkan.metastore.service');
    $dataset = $this->validMetadataFactory->get($this->getDataset(123, 'Test #1', ['district_centerpoints_small.csv']), 'dataset');
    $service->post('dataset', $dataset);
    $dataset2 = $this->validMetadataFactory->get($this->getDataset(456, 'Test #2', ['district_centerpoints_small.csv']), 'dataset');
    $service->post('dataset', $dataset2);
    $this->runQueues(['datastore_import']);
    $service->delete('dataset', 123);
    $success = $this->runQueues(['orphan_reference_processor']);
    $this->assertNull($success);
  }

}
