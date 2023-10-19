<?php

namespace Drupal\Tests\harvest\Kernel\Storage;

use Drupal\harvest\Entity\HarvestPlanEntityDatabaseTable;
use Drupal\KernelTests\KernelTestBase;

/**
 * @covers \Drupal\harvest\Storage\DatabaseTableFactory
 *
 * @group harvest
 * @group kernel
 */
class DatabaseTableFactoryTest extends KernelTestBase {

  protected static $modules = [
    'common',
    'harvest',
    'metastore',
  ];

  public function test() {
    /** @var \Drupal\harvest\Storage\DatabaseTableFactory $factory */
    $factory = $this->container->get('dkan.harvest.storage.database_table');
    // We only get a harvest plan entity db table if we specify the correct
    // table name.
    $this->assertNotInstanceOf(
      HarvestPlanEntityDatabaseTable::class,
      $factory->getInstance('blah', [])
    );
    $this->assertInstanceOf(
      HarvestPlanEntityDatabaseTable::class,
      $factory->getInstance('harvest_plans', [])
    );
  }

}
