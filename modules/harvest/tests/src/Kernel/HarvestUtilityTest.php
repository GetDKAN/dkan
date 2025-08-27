<?php

declare(strict_types=1);

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
    $this->installEntitySchema('harvest_hash');
    $this->installEntitySchema('harvest_run');
  }

  public function testOrphanHarvestData() {
    $existing_plan_id = 'testplanid';
    $entity_orphan_plan_id = 'entityorphanplanid';
    $table_orphan_plan_id = 'tableorphanplanid';

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

  public function testConvertHashTable() {
    $this->installEntitySchema('harvest_hash');
    $harvest_plan_id = 'TEST';
    /** @var \Drupal\harvest\Storage\DatabaseTable $old_hash_table */
    $old_hash_table = $this->container
      ->get('dkan.harvest.storage.database_table')
      ->getInstance('harvest_' . $harvest_plan_id . '_hashes');
    /** @var \Drupal\harvest\Storage\HarvestHashesEntityDatabaseTable $new_hash_table */
    $new_hash_table = $this->container
      ->get('dkan.harvest.storage.hashes_database_table')
      ->getInstance($harvest_plan_id);
    /** @var \Drupal\Component\Uuid\UuidInterface $uuid_generator */
    $uuid_generator = $this->container->get('uuid');

    // Fill an old-style hash table.
    $iterations = 5;
    foreach (range(1, $iterations) as $iteration) {
      $old_hash_table->store(json_encode((object) [
        'harvest_plan_id' => $harvest_plan_id,
        'hash' => uniqid(),
      ]), $uuid_generator->generate());
    }

    // Convert the table.
    /** @var \Drupal\harvest\HarvestUtility $harvest_utility */
    $harvest_utility = $this->container->get('dkan.harvest.utility');
    $harvest_utility->convertHashTable($harvest_plan_id);

    // Assert the converted table using DatabaseTableInterface.
    $this->assertCount($iterations, $ids = $new_hash_table->retrieveAll());
    foreach ($ids as $id) {
      $data = json_decode($new_hash_table->retrieve($id), TRUE);
      $this->assertEquals($harvest_plan_id, $data['harvest_plan_id'] ?? 'FAIL');
    }

    // Assert the converted table using entity API.
    $entity_storage = $this->container->get('entity_type.manager')
      ->getStorage('harvest_hash');
    $this->assertCount($iterations, $entities = $entity_storage->loadMultiple());
    foreach ($entities as $entity) {
      $this->assertEquals($harvest_plan_id, $entity->get('harvest_plan_id')->getString());
    }
  }

  /**
   * @covers ::harvestHashUpdate
   */
  public function testHarvestHashUpdate() {
    // Use the old-style factory to create an orphaned harvest hash table.
    $orphaned_plan_id = 'orphanage';
    $orphaned_uuid = '24614086-9E33-4B09-B5B8-2ACAFE341AF9';
    $orphaned_hash = '1234567890';
    /** @var \Drupal\harvest\Storage\DatabaseTableFactory $table_factory */
    $table_factory = $this->container->get('dkan.harvest.storage.database_table');
    $orphaned_table = $table_factory->getInstance('harvest_' . $orphaned_plan_id . '_hashes');
    $this->assertCount(0, $orphaned_table->retrieveAll());
    $orphaned_table->store(json_encode((object) [
      'hash' => $orphaned_hash,
      'harvest_plan_id' => $orphaned_plan_id,
    ]), $orphaned_uuid);

    // Update the table using the utility.
    /** @var \Drupal\harvest\HarvestUtility $harvest_utility */
    $harvest_utility = $this->container->get('dkan.harvest.utility');
    $harvest_utility->harvestHashUpdate();

    // Check that it happened.
    /** @var \Drupal\harvest\Storage\HarvestHashesEntityDatabaseTable $new_hash_table */
    $new_hash_table = $this->container
      ->get('dkan.harvest.storage.hashes_database_table')
      ->getInstance($orphaned_plan_id);
    $this->assertCount(1, $new_hash_table->retrieveAll());
    $this->assertNotNull($retrieved = $new_hash_table->retrieve($orphaned_uuid));
    // Should be stored as an object.
    $this->assertIsObject(json_decode($retrieved));
    // Convert to array for convenience.
    $retrieved = json_decode($retrieved, TRUE);
    $this->assertEquals($orphaned_hash, $retrieved['hash'] ?? 'no hash');
  }

  /**
   * @covers ::harvestRunsUpdate
   */
  public function testHarvestRunsUpdate() {
    // Use the old-style factory to create an orphaned harvest runs table.
    $orphaned_plan_id = 'orphanage';
    $orphaned_id = '1711038292';
    $orphaned_result = [
      'plan' => '{"plan": "json"}',
      'status' => [
        'extract' => 'AWESOME',
      ],
      'identifier' => $orphaned_id,
    ];
    $orphaned_table = $this->container
      ->get('dkan.harvest.storage.database_table')
      ->getInstance('harvest_' . $orphaned_plan_id . '_runs');
    $this->assertCount(0, $orphaned_table->retrieveAll());
    $orphaned_table->store(json_encode($orphaned_result), $orphaned_id);

    // Update the table using the utility.
    /** @var \Drupal\harvest\HarvestUtility $harvest_utility */
    $harvest_utility = $this->container->get('dkan.harvest.utility');
    $harvest_utility->harvestRunsUpdate();

    // Check that it happened.
    /** @var \Drupal\harvest\Entity\HarvestRunRepository $new_runs_repository */
    $new_runs_repository = $this->container->get('dkan.harvest.storage.harvest_run_repository');
    $this->assertCount(1, $new_runs_repository->retrieveAllRunIds($orphaned_plan_id));
    /** @var \Drupal\harvest\Entity\HarvestRun $run_entity */
    $this->assertNotNull(
      $run_entity = $new_runs_repository->loadEntity($orphaned_plan_id, $orphaned_id)
    );
    $this->assertEquals('AWESOME', $run_entity->get('extract_status')->getString());
  }

}
