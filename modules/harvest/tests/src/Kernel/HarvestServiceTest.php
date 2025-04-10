<?php

declare(strict_types=1);

namespace Drupal\Tests\harvest\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\harvest\ETL\Extract\DataJson;
use Drupal\harvest\ETL\Load\Simple;

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

  protected function setUp() : void {
    parent::setUp();
    $this->installEntitySchema('harvest_plan');
    $this->installEntitySchema('harvest_hash');
    $this->installEntitySchema('harvest_run');
  }

  public function testGetAllHarvestIds() {
    /** @var \Drupal\harvest\HarvestService $harvest_service */
    $harvest_service = $this->container->get('dkan.harvest.service');

    foreach (['100', '102', '101'] as $identifier) {
      $plan = (object) [
        'identifier' => $identifier,
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
      $this->assertEquals(
        $identifier,
        $harvest_service->registerHarvest($plan)
      );
    }
    $this->assertEquals(
      ['100', '101', '102'],
      array_values($harvest_service->getAllHarvestIds())
    );
  }

  public function testPlanWithChangingDataset() {
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

    // Check the round-trip to the database.
    /** @var \Drupal\datastore\Storage\DatabaseTableFactory $harvest_storage_factory */
    $harvest_storage_factory = $this->container->get('dkan.harvest.storage.database_table');
    $this->assertNotEmpty(
      $storedTestPlanJson = $harvest_storage_factory
        ->getInstance('harvest_plans')
        ->retrieve($plan_identifier)
    );
    $storedTestPlan = json_decode($storedTestPlanJson);
    $this->assertEquals('test_plan', $storedTestPlan->identifier);

    // Run the harvest.
    $result = $harvest_service->runHarvest($plan_identifier);
    // Check the results.
    $this->assertEquals('SUCCESS', $result['status']['extract'] ?? 'not success');
    $this->assertCount(2, $result['status']['extracted_items_ids'] ?? []);
    $this->assertArrayNotHasKey('errors', $result);
    $this->assertEquals(json_encode(['NEW', 'NEW']), json_encode(array_values($result['status']['load'])));

    // Check the items table.
    $storedObject = $harvest_storage_factory
      ->getInstance('harvest_test_plan_items')
      ->retrieve('cedcd327-4e5d-43f9-8eb1-c11850fa7c55');
    $this->assertIsString($storedObject);
    $storedObject = json_decode($storedObject);
    $this->assertIsObject($storedObject);

    // Check the hashes.
    /** @var \Drupal\harvest\Storage\HarvestHashesEntityDatabaseTable $hash_table */
    $hash_table = $this->container
      ->get('dkan.harvest.storage.hashes_database_table')
      ->getInstance($plan_identifier);
    $this->assertCount(2, $hash_table->retrieveAll());
    // Get the hashes for comparison later. Bike lanes will change later.
    $this->assertNotEmpty(
      $bike_lanes_hash = json_decode($hash_table->retrieve('cedcd327-4e5d-43f9-8eb1-c11850fa7c55'))->hash ?? NULL
    );
    $this->assertNotEmpty(
      $deprivation_hash = json_decode($hash_table->retrieve('fb3525f2-d32a-451e-8869-906ed41f7695'))->hash ?? NULL
    );

    // Run harvest again, no changes.
    $result = $harvest_service->runHarvest('test_plan');
    $this->assertEquals('SUCCESS', $result['status']['extract']);
    $this->assertCount(2, $result['status']['extracted_items_ids'] ?? []);
    $this->assertEquals(json_encode(['UNCHANGED', 'UNCHANGED']), json_encode(array_values($result['status']['load'])));

    // Change the harvest plan to a new harvest for the same dataset IDs.
    $plan->extract->uri = 'file://' . realpath(__DIR__ . '/../../files/data2.json');

    // Run the harvest again with changes.
    $this->assertEquals(
      $plan_identifier,
      $harvest_service->registerHarvest($plan)
    );
    $result = $harvest_service->runHarvest($plan_identifier);
    // Check the result.
    $this->assertEquals('SUCCESS', $result['status']['extract'] ?? 'no success');
    $this->assertCount(2, $result['status']['extracted_items_ids'] ?? []);
    $this->assertEquals(
      ['UPDATED', 'UNCHANGED'],
      array_values($result['status']['load'])
    );

    // Check the items table.
    $storedObject = $harvest_storage_factory
      ->getInstance('harvest_' . $plan_identifier . '_items')
      ->retrieve('cedcd327-4e5d-43f9-8eb1-c11850fa7c55');
    $this->assertIsString($storedObject);
    $storedObject = json_decode($storedObject);
    $this->assertIsObject($storedObject);
    $this->assertEquals('Florida Bike Lanes 2', $storedObject->title ?? 'not bike lanes');

    // Verify the hash changed.
    $this->assertCount(2, $hash_table->retrieveAll());
    // This is the datastore that should have changed.
    $this->assertNotEquals(
      $bike_lanes_hash,
      json_decode($hash_table->retrieve('cedcd327-4e5d-43f9-8eb1-c11850fa7c55'))->hash ?? NULL
    );
    // This datastore will stay the same.
    $this->assertEquals(
      $deprivation_hash,
      json_decode($hash_table->retrieve('fb3525f2-d32a-451e-8869-906ed41f7695'))->hash ?? NULL
    );

    // Revert harvest.
    $harvest_service->revertHarvest($plan_identifier);
    $storageTypes = [
      'harvest_' . $plan_identifier . '_items',
      'harvest_' . $plan_identifier . '_runs',
    ];
    foreach ($storageTypes as $storageId) {
      $this->assertCount(
        0,
        $harvest_storage_factory->getInstance($storageId)->retrieveAll()
      );
    }
    $this->assertCount(0, $hash_table->retrieveAll());

    // Deregister harvest.
    $harvest_service->deregisterHarvest($plan_identifier);
    $this->assertCount(
      0,
      $harvest_storage_factory->getInstance('harvest_plans')->retrieveAll()
    );
  }

  /**
   * @covers ::getHarvestRunResult
   */
  public function testGetHarvestRunResult() {
    // There should be no harvest runs at the beginning of this test method, so
    // getHarvestRunResult should return an empty array.
    /** @var \Drupal\harvest\HarvestService $harvest_service */
    $harvest_service = $this->container->get('dkan.harvest.service');
    $any_harvest_run_id = '111';
    $this->assertEquals([], $harvest_service->getHarvestRunResult('any_plan', $any_harvest_run_id));

    // Register a harvest and run it.
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
    $this->assertEquals($plan_identifier, $harvest_service->registerHarvest($plan));

    $run_result = $harvest_service->runHarvest($plan_identifier);
    $this->assertNotEmpty($timestamp = $run_result['identifier']);

    // Compare the reloaded results to the ones from the original run.
    $this->assertEquals($run_result, $harvest_service->getHarvestRunResult($plan_identifier, $timestamp));
  }

}
