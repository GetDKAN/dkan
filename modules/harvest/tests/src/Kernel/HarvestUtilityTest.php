<?php

namespace Drupal\Tests\harvest\Kernel;

use Drupal\KernelTests\KernelTestBase;

/**
 * @covers \Drupal\harvest\HarvestUtility
 * @coversDefaultClass \Drupal\harvest\HarvestUtility
 *
 * @group dkan
 * @group harvest
 * @group kernel
 */
class HarvestUtilityTest extends KernelTestBase {

  protected static $modules = [
    'common',
    'harvest',
    'metastore',
  ];

  protected function setUp() : void {
    parent::setUp();
    $this->installEntitySchema('harvest_plan');
  }

  public function test() {
    $existing_plan_id = 'testplanid';
    $orphan_plan_id = 'orphanplanid';

    /** @var \Drupal\harvest\HarvestService $harvest_service */
    $harvest_service = $this->container->get('dkan.harvest.service');

    // Use a database table to store a fake plan so we don't have to actually
    // store a plan.
    /** @var \Drupal\harvest\Storage\DatabaseTableFactory $table_factory */
    $table_factory = $this->container->get('dkan.harvest.storage.database_table');
    /** @var \Drupal\harvest\Storage\DatabaseTable $plan_storage */
    $plan_storage = $table_factory->getInstance('harvest_plans');
    $plan_storage->store('{we do not have a plan}', $existing_plan_id);

    // Getting all harvest run info for a non-existent plan results in a run
    // table being created.
    // @todo This is probably something that needs fixing.
    $harvest_service->getAllHarvestRunInfo($orphan_plan_id);

    /** @var \Drupal\harvest\HarvestUtility $harvest_utility */
    $harvest_utility = $this->container->get('dkan.harvest.utility');
    $orphaned = $harvest_utility->findOrphanedHarvestDataIds();
    $this->assertNotContains($existing_plan_id, $orphaned);
    $this->assertEquals([$orphan_plan_id => $orphan_plan_id], $orphaned);

    // Remove the orphans.
    foreach ($orphaned as $orphan) {
      $harvest_utility->destructOrphanTables($orphan);
    }
    $this->assertEmpty(
      $this->container->get('database')->schema()
        ->findTables('harvest_' . $orphan_plan_id . '%')
    );
  }

}
