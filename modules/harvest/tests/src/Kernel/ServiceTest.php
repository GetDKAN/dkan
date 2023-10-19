<?php

namespace Drupal\Tests\harvest\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\common\Traits\ServiceCheckTrait;
use Harvest\ETL\Extract\DataJson;
use Harvest\ETL\Load\Simple;

/**
 * @coversDefaultClass \Drupal\harvest\HarvestService
 *
 * @group harvest
 * @group kernel
 *
 * @see \Drupal\Tests\harvest\Unit\ServiceTest
 */
class ServiceTest extends KernelTestBase {
  use ServiceCheckTrait;

  private $storageFactory;

  protected static $modules = [
    'common',
    'harvest',
    'metastore',
    'node',
    'user',
  ];

  public function test() {
    $this->installEntitySchema('harvest_plan');
    $this->installEntitySchema('node');
    $this->installEntitySchema('user');

    /** @var \Drupal\harvest\HarvestService $service */
    $service = $this->container->get('dkan.harvest.service');

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
    $harvest_id = $service->registerHarvest($plan);

    $this->assertEquals('test_plan', $harvest_id);

    /** @var \Drupal\datastore\Storage\DatabaseTableFactory $harvest_storage_factory */
    $harvest_storage_factory = $this->container->get('dkan.harvest.storage.database_table');
    $this->assertNotEmpty(
      $storedTestPlanJson = $harvest_storage_factory
        ->getInstance('harvest_plans')
        ->retrieve($harvest_id)
    );
    $storedTestPlan = json_decode($storedTestPlanJson);
    $this->assertEquals('test_plan', $storedTestPlan->identifier);

    // Run a harvest.
    $result = $service->runHarvest('test_plan');

    $this->assertEquals('SUCCESS', $result['status']['extract']);
    $this->assertEquals(2, count($result['status']['extracted_items_ids']));
    $this->assertEquals(json_encode(['NEW', 'NEW']), json_encode(array_values($result['status']['load'])));

    $storedObject = $harvest_storage_factory->getInstance('harvest_test_plan_items')->retrieve('cedcd327-4e5d-43f9-8eb1-c11850fa7c55');
    $this->assertTrue(is_string($storedObject));
    $storedObject = json_decode($storedObject);
    $this->assertTrue(is_object($storedObject));

    // Run harvest again, no changes.
    $result = $service->runHarvest('test_plan');

    $this->assertEquals('SUCCESS', $result['status']['extract']);
    $this->assertEquals(2, count($result['status']['extracted_items_ids']));
    $this->assertEquals(json_encode(['UNCHANGED', 'UNCHANGED']), json_encode(array_values($result['status']['load'])));

    // Run harvest with changes.
    $plan2 = clone $plan;
    $plan2->extract->uri = 'file://' . __DIR__ . '/../../files/data2.json';
    $service->registerHarvest($plan2);
    $result = $service->runHarvest('test_plan');

    $this->assertEquals('SUCCESS', $result['status']['extract']);
    $this->assertEquals(2, count($result['status']['extracted_items_ids']));
    $this->assertEquals(json_encode(['UPDATED', 'UNCHANGED']), json_encode(array_values($result['status']['load'])));

    $storedObject = $harvest_storage_factory->getInstance('harvest_test_plan_items')->retrieve('cedcd327-4e5d-43f9-8eb1-c11850fa7c55');
    $this->assertTrue(is_string($storedObject));
    $storedObject = json_decode($storedObject);
    $this->assertTrue(is_object($storedObject));
    $this->assertEquals('Florida Bike Lanes 2', $storedObject->title);

    // Revert harvest.
    $service->revertHarvest('test_plan');
    $storageTypes = [
      'harvest_test_plan_items',
      'harvest_test_plan_hashes',
      'harvest_test_plan_runs',
    ];
    foreach ($storageTypes as $storageId) {
      $this->assertCount(
        0,
        $harvest_storage_factory->getInstance($storageId)->retrieveAll()
      );
    }

    // Deregister harvest.
    $service->deregisterHarvest('test_plan');
    $this->assertCount(
      0,
      $harvest_storage_factory->getInstance('harvest_plans')->retrieveAll()
    );
  }

}
