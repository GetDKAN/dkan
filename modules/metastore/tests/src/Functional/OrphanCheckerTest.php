<?php

namespace Drupal\Tests\metastore\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\common\Traits\GetDataTrait;
use Drupal\Tests\common\Traits\QueueRunnerTrait;
use Drupal\Tests\metastore\Unit\MetastoreServiceTest;

/**
 * @group dkan
 * @group metastore
 * @group functional
 * @group btb
 * @group functional1
 */
class OrphanCheckerTest extends BrowserTestBase {
  use GetDataTrait;
  use QueueRunnerTrait;

  protected static $modules = [
    'datastore',
    'metastore',
    'node',
  ];

  protected $defaultTheme = 'stark';

  public function test() {
    $validMetadataFactory = MetastoreServiceTest::getValidMetadataFactory($this);
    /** @var \Drupal\metastore\MetastoreService $service */
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
