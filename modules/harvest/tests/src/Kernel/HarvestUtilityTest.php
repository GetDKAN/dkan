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
    $this->installEntitySchema('harvest_hash');
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

}
