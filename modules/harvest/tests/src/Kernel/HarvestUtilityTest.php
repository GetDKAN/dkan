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
    $this->installEntitySchema('harvest_run');
  }

  public function testOrphanHarvestData() {
    $existing_plan_id = 'testplanid';
    $entity_orphan_plan_id = 'entityorphanplanid';
    $table_orphan_plan_id = 'tableorphanplanid';

    /** @var \Drupal\harvest\HarvestService $harvest_service */
    $harvest_service = $this->container->get('dkan.harvest.service');

    // Use a database table to store a fake plan. The plan entity is the same
    // schema as the old table management.
    /** @var \Drupal\harvest\Entity\HarvestPlanRepository $plan_repository */
    $plan_repository = $this->container->get('dkan.harvest.harvest_plan_repository');
    $plan_repository->storePlan((object) ['no' => 'plan'], $existing_plan_id);

    // Create harvest run data with a different plan ID, using entity API.
    /** @var \Drupal\harvest\Entity\HarvestRunRepository $run_repository */
    $run_repository = $this->container->get('dkan.harvest.storage.harvest_run_repository');
    $run_id = (string) time();
    $run_repository->storeRun(['status' => ['extract' => 'neeto']], $entity_orphan_plan_id, $run_id);
    $this->assertNotNull($run_repository->retrieveRunJson($entity_orphan_plan_id, $run_id));

    // Create harvest run data with a different plan ID, using table API.
    /** @var \Drupal\harvest\Storage\DatabaseTableFactory $table_factory */
    $table_factory = $this->container->get('dkan.harvest.storage.database_table');
    $run_table = $table_factory->getInstance('harvest_' . $table_orphan_plan_id . '_runs');
    $run_table->store('{"fake_json"}', $run_id);
    $this->assertNotNull($run_table->retrieve($run_id));

    /** @var \Drupal\harvest\HarvestUtility $harvest_utility */
    $harvest_utility = $this->container->get('dkan.harvest.utility');
    $orphaned = $harvest_utility->findOrphanedHarvestDataIds();
    $this->assertNotContains($existing_plan_id, $orphaned);
    foreach ([$entity_orphan_plan_id, $table_orphan_plan_id] as $orphan_plan_id) {
      $this->assertArrayHasKey($orphan_plan_id, $orphaned);
      $this->assertContains($orphan_plan_id, $orphaned);
    }

    // Remove the orphans.
    foreach ($orphaned as $orphan) {
      $harvest_utility->destructOrphanTables($orphan);
    }
    // DestructOrphanTables() only removes old-style harvest_id_runs type
    // tables.
    foreach ([$entity_orphan_plan_id, $table_orphan_plan_id] as $orphan_plan_id) {
      $this->assertEmpty(
      $this->container->get('database')->schema()
        ->findTables('harvest_' . $orphan_plan_id . '%')
      );
    }
    // Entity-based run orphans still remain. We don't need the utility service
    // to remove them, so we don't test that here.
    $this->assertContains(
      $entity_orphan_plan_id,
      $harvest_utility->findOrphanedHarvestDataIds()
    );
  }

}
