<?php

declare(strict_types=1);

namespace Drupal\Tests\harvest\Kernel\Storage;

use Drupal\KernelTests\KernelTestBase;

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
  ];

  protected function setUp() : void {
    parent::setUp();
    $this->installEntitySchema('harvest_hash');
  }

  /**
   * @covers ::store
   */
  public function testStoreIdMismatch() {
    $json_id = 'json_id';
    $table_id = 'different_id';
    /** @var \Drupal\harvest\Storage\HarvestHashesEntityDatabaseTable $table */
    $table = $this->container
      ->get('dkan.harvest.storage.hashes_database_table')
      ->getInstance($table_id);
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Encoded JSON plan identifier: ' . $json_id . ' must match table plan identifier: ' . $table_id);
    $table->store(json_encode(['harvest_plan_id' => $json_id]), NULL);
  }

  /**
   * @covers ::store
   * @covers ::retrieve
   * @covers ::retrieveAll
   * @covers ::destruct
   * @covers ::count
   * @covers ::loadEntity
   */
  public function testCrud() {
    $dataset_uuid = '9110349C-65CB-4187-984C-E33A0F27DA39';
    $harvest_plan_id = 'test_harvest';
    $hash_data = [
      'harvest_plan_id' => $harvest_plan_id,
      'hash' => 'yuck',
    ];
    /** @var \Drupal\harvest\Storage\HarvestHashesEntityDatabaseTable $table */
    $table = $this->container
      ->get('dkan.harvest.storage.hashes_database_table')
      ->getInstance($harvest_plan_id);

    // The first call will create the entity.
    $this->assertEquals(0, $table->count());
    $this->assertEquals($dataset_uuid, $table->store(json_encode($hash_data), $dataset_uuid));
    $this->assertEquals(1, $table->count());
    $this->assertContains($dataset_uuid, $table->retrieveAll());
    $data = json_decode($table->retrieve($dataset_uuid), TRUE);
    $this->assertEquals('yuck', $data['hash'] ?? 'not yuck');

    // The second call will update.
    $hash_data['hash'] = 'yum';
    $this->assertEquals($dataset_uuid, $table->store(json_encode($hash_data), $dataset_uuid));
    $this->assertEquals(1, $table->count());
    $this->assertContains($dataset_uuid, $table->retrieveAll());
    $data = json_decode($table->retrieve($dataset_uuid), TRUE);
    $this->assertEquals('yum', $data['hash'] ?? 'not yum');

    // Add another dataset.
    $dataset2_uuid = '5EAC66F4-4B4A-4E25-AC4F-28220B8316AE';
    $this->assertEquals($dataset2_uuid, $table->store(json_encode($hash_data), $dataset2_uuid));
    $this->assertEquals(2, $table->count());
    $all = $table->retrieveAll();
    $this->assertCount(2, $all);
    $this->assertContains($dataset2_uuid, $all);

    // Remove the first one.
    $this->assertEquals($dataset_uuid, $table->remove($dataset_uuid));
    $this->assertEquals(1, $table->count());
    $all = $table->retrieveAll();
    $this->assertCount(1, $all);
    $this->assertNotContains($dataset_uuid, $all);
    $this->assertContains($dataset2_uuid, $all);

    // Finally destruct everything.
    $table->destruct();
    $this->assertCount(0, $table->retrieveAll());
    $this->assertEquals(0, $table->count());
  }

}
