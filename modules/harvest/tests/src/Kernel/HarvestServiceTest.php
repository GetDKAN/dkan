<?php

namespace Drupal\Tests\harvest\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Harvest\ETL\Extract\DataJson;
use Harvest\ETL\Load\Simple;

/**
 * @covers \Drupal\harvest\HarvestService
 * @coversDefaultClass \Drupal\harvest\HarvestService
 *
 * @group dkan
 * @group harvest
 * @group kernel
 */
class HarvestServiceTest extends KernelTestBase {

  protected static $modules = [
    'common',
    'harvest',
    'metastore',
    'node',
  ];

  public function testPlan() {
    /** @var \Drupal\harvest\HarvestService $harvest_service */
    $harvest_service = $this->container->get('dkan.harvest.service');
    $storage_factory = $this->container->get('dkan.harvest.storage.database_table');

    $plan = (object) [
      'identifier' => 'test_plan',
      'extract' => (object) [
        'type' => DataJson::class,
        'uri' => 'file://' . __DIR__ . '/../../files/data.json',
      ],
      'transforms' => [],
      'load' => (object) [
        'type' => Simple::class,
      ],
    ];

    // Register a harvest.
    $result = $harvest_service->registerHarvest($plan);

    $this->assertEquals('test_plan', $result);

    $storedTestPlan = json_decode($storage_factory->getInstance('harvest_plans')->retrieve('test_plan'));
    $this->assertEquals('test_plan', $storedTestPlan->identifier);

    // Run a harvest.
    $result = $harvest_service->runHarvest('test_plan');

    $this->assertEquals('SUCCESS', $result['status']['extract']);
    $this->assertEquals(2, count($result['status']['extracted_items_ids']));
    $this->assertEquals(json_encode(['NEW', 'NEW']), json_encode(array_values($result['status']['load'])));

    $storedObject = $storage_factory->getInstance('harvest_test_plan_items')->retrieve('cedcd327-4e5d-43f9-8eb1-c11850fa7c55');
    $this->assertTrue(is_string($storedObject));
    $storedObject = json_decode($storedObject);
    $this->assertTrue(is_object($storedObject));

    // Run harvest again, no changes.
    $result = $harvest_service->runHarvest('test_plan');

    $this->assertEquals('SUCCESS', $result['status']['extract']);
    $this->assertEquals(2, count($result['status']['extracted_items_ids']));
    $this->assertEquals(json_encode(['UNCHANGED', 'UNCHANGED']), json_encode(array_values($result['status']['load'])));

    // Run harvest with changes.
    $plan2 = clone $plan;
    $plan2->extract->uri = 'file://' . __DIR__ . '/../../files/data2.json';
    $harvest_service->registerHarvest($plan2);
    $result = $harvest_service->runHarvest('test_plan');

    $this->assertEquals('SUCCESS', $result['status']['extract']);
    $this->assertEquals(2, count($result['status']['extracted_items_ids']));
    $this->assertEquals(json_encode(['UPDATED', 'UNCHANGED']), json_encode(array_values($result['status']['load'])));

    $storedObject = $storage_factory->getInstance('harvest_test_plan_items')->retrieve('cedcd327-4e5d-43f9-8eb1-c11850fa7c55');
    $this->assertTrue(is_string($storedObject));
    $storedObject = json_decode($storedObject);
    $this->assertTrue(is_object($storedObject));
    $this->assertEquals('Florida Bike Lanes 2', $storedObject->title);

    /** @var \Drupal\Core\Database\Schema $schema */
    $schema = $this->container->get('database')->schema();

    // Reverting the harvest should leave behind the items and hashes tables,
    // but remove the runs table.
    $harvest_service->revertHarvest('test_plan');
    foreach ([
      'harvest_test_plan_items',
      'harvest_test_plan_hashes',
    ] as $storageId) {
      $this->assertTrue($schema->tableExists($storageId), $storageId . ' does not exist.');
    }
    $this->assertFalse($schema->tableExists('harvest_test_plan_runs', 'harvest_test_plan_runs exists.'));

    // All these tables should be empty. The runs table will be re-created
    // as a side effect of calling retrieveAll() on it.
    $storageTables = [
      'harvest_test_plan_items',
      'harvest_test_plan_hashes',
      'harvest_test_plan_runs',
    ];
    foreach ($storageTables as $storageId) {
      $this->assertCount(0, $storage_factory->getInstance($storageId)->retrieveAll());
    }

    // Deregister harvest.
    $harvest_service->deregisterHarvest('test_plan');
    $this->assertNull($harvest_service->getHarvestPlan('test_plan'));
    $this->assertNotContains('test_plan', $harvest_service->getAllHarvestIds());
    // Check the data tables. They should have been removed.
    foreach ($storageTables as $storageId) {
      $this->assertFalse($schema->tableExists($storageId), $storageId . ' exists.');
    }
  }

  public function testHashesForChangingDataset() {
    // Register a harvest.
    /** @var \Drupal\harvest\HarvestService $harvest_service */
    $harvest_service = $this->container->get('dkan.harvest.service');
    $plan_identifier = 'test_plan';
    $plan = (object) [
      'identifier' => $plan_identifier,
      'extract' => (object) [
        'type' => DataJson::class,
        'uri' => 'file://' . realpath(__DIR__ . '/../../files/data.json'),
      ],
      'transforms' => [],
      'load' => (object) [
        'type' => Simple::class,
      ],
    ];
    $this->assertEquals(
      $plan_identifier,
      $harvest_service->registerHarvest($plan)
    );

    // Run the harvest.
    $result = $harvest_service->runHarvest($plan_identifier);
    $this->assertEquals('SUCCESS', $result['status']['extract'] ?? 'not success');
    $this->assertArrayNotHasKey('errors', $result);

    // Check the hashes.
    /** @var \Drupal\harvest\Storage\DatabaseTable $hash_table */
    $hash_table = $this->container
      ->get('dkan.harvest.storage.database_table')
      ->getInstance('harvest_' . $plan_identifier . '_hashes');
    $this->assertCount(2, $hash_table->retrieveAll());
    // Get the hashes for comparison later. Bike lanes will change later.
    $this->assertNotEmpty(
      $bike_lanes_hash = json_decode($hash_table->retrieve('cedcd327-4e5d-43f9-8eb1-c11850fa7c55'))->hash ?? NULL
    );
    $this->assertNotEmpty(
      $deprivation_hash = json_decode($hash_table->retrieve('fb3525f2-d32a-451e-8869-906ed41f7695'))->hash ?? NULL
    );

    // Change the harvest plan to a new harvest for the same dataset IDs.
    $plan->extract->uri = 'file://' . realpath(__DIR__ . '/../../files/data2.json');
    $harvest_service->registerHarvest($plan);

    // Run the harvest.
    $harvest_service->runHarvest($plan_identifier);

    // Verify the hash changed.
    $this->assertCount(2, $hash_table->retrieveAll());
    // This is the datastore that will change.
    $this->assertNotEquals(
      $bike_lanes_hash,
      json_decode($hash_table->retrieve('cedcd327-4e5d-43f9-8eb1-c11850fa7c55'))->hash ?? NULL
    );
    // This one will stay the same.
    $this->assertEquals(
      $deprivation_hash,
      json_decode($hash_table->retrieve('fb3525f2-d32a-451e-8869-906ed41f7695'))->hash ?? NULL
    );
  }

}
