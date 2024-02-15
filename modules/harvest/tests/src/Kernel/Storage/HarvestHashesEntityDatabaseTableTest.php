<?php

namespace Drupal\Tests\harvest\Kernel\Storage;

use Drupal\KernelTests\KernelTestBase;
use Harvest\ETL\Extract\DataJson;
use Harvest\ETL\Load\Simple;

/**
 * @covers \Drupal\harvest\Storage\HarvestHashesEntityDatabaseTable
 * @coversDefaultClass \Drupal\harvest\Storage\HarvestHashesEntityDatabaseTable
 *
 * @group dkan
 * @group harvest
 * @group kernel
 */
class HarvestHashesEntityDatabaseTableTest extends KernelTestBase {

  protected static $modules = [
    'common',
    'harvest',
    'metastore',
    'node',
  ];

  protected function setUp() : void {
    parent::setUp();
    $this->installEntitySchema('harvest_hash');
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
        'uri' => 'file://' . realpath(__DIR__ . '/../../../files/data.json'),
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
    /** @var \Drupal\harvest\Storage\HarvestHashesEntityDatabaseTable $hash_table */
    $hash_table = $this->container
      ->get('dkan.harvest.storage.hashes_database_table')
      ->getInstance($plan_identifier);
    $this->assertCount(2, $hash_ids = $hash_table->retrieveAll());
    $first_hashes = [];
    foreach ($hash_ids as $id) {
      $first_hashes[$id] = $hash_table->retrieve($id);
    }

    // Change the harvest plan to a new harvest for the same dataset IDs.
    $plan->extract->uri = 'file://' . realpath(__DIR__ . '/../../../files/data2.json');
    $harvest_service->registerHarvest($plan);

    // Run the harvest.
    $harvest_service->runHarvest($plan_identifier);

    // Verify the hash changed.
    $this->assertCount(2, $hash_ids = $hash_table->retrieveAll());
    $second_hashes = [];
    foreach ($hash_ids as $id) {
      $second_hashes[$id] = $hash_table->retrieve($id);
    }
    $this->assertNotEquals($first_hashes, $second_hashes);
  }

}
