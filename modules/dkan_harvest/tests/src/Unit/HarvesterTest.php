<?php

use Harvest\ETL\Load\Simple;
use Harvest\ETL\Extract\DataJson;
use PHPUnit\Framework\TestCase;
use MockChain\Chain;
use Contracts\FactoryInterface;
use Contracts\Mock\Storage\Memory;
use Drupal\dkan_datastore\Storage\DatabaseTable;
use Drupal\dkan_harvest\Storage\DatabaseTableFactory;
use Drupal\dkan_harvest\Harvester as HarvestService;

/**
 *
 */
class HarvesterTest extends TestCase {

  private $storageFactory;

  /**
   *
   */
  public function test() {
    $service = new HarvestService($this->getStorageFactory());

    $plan = (object) [
      'identifier' => 'test_plan',
      'extract' => (object) [
        "type" => DataJson::class,
        "uri" => "file://" . __DIR__ . '/../../files/data.json',
      ],
      'transforms' => [],
      'load' => (object) [
        "type" => Simple::class,
      ],
    ];

    // Register a harvest.
    $result = $service->registerHarvest($plan);

    $this->assertEquals('test_plan', $result);

    $storedTestPlan = json_decode($this->getStorageFactory()->getInstance('harvest_plans')->retrieve('test_plan'));
    $this->assertEquals('test_plan', $storedTestPlan->identifier);

    // Run a harvest.
    $result = $service->runHarvest('test_plan');

    $this->assertEquals("SUCCESS", $result['status']['extract']);
    $this->assertEquals(2, count($result['status']['extracted_items_ids']));
    $this->assertEquals(json_encode(["NEW", "NEW"]), json_encode(array_values($result['status']['load'])));

    $storedObject = $this->getStorageFactory()->getInstance('harvest_test_plan_items')->retrieve("cedcd327-4e5d-43f9-8eb1-c11850fa7c55");
    $this->assertTrue(is_string($storedObject));
    $storedObject = json_decode($storedObject);
    $this->assertTrue(is_object($storedObject));

    // Run havest again, no changes.
    $result = $service->runHarvest('test_plan');

    $this->assertEquals("SUCCESS", $result['status']['extract']);
    $this->assertEquals(2, count($result['status']['extracted_items_ids']));
    $this->assertEquals(json_encode(["UNCHANGED", "UNCHANGED"]), json_encode(array_values($result['status']['load'])));

    // Run harvest with changes.
    $plan2 = clone $plan;
    $plan2->extract->uri = "file://" . __DIR__ . '/../../files/data2.json';
    $service->registerHarvest($plan2);
    $result = $service->runHarvest('test_plan');

    $this->assertEquals("SUCCESS", $result['status']['extract']);
    $this->assertEquals(2, count($result['status']['extracted_items_ids']));
    $this->assertEquals(json_encode(["UPDATED", "UNCHANGED"]), json_encode(array_values($result['status']['load'])));

    $storedObject = $this->getStorageFactory()->getInstance('harvest_test_plan_items')->retrieve("cedcd327-4e5d-43f9-8eb1-c11850fa7c55");
    $this->assertTrue(is_string($storedObject));
    $storedObject = json_decode($storedObject);
    $this->assertTrue(is_object($storedObject));
    $this->assertEquals("Florida Bike Lanes 2", $storedObject->title);

    // Revert harvest.
    $service->revertHarvest('test_plan');
    $storageTypes = [
      'harvest_test_plan_items',
      'harvest_test_plan_hashes',
      'harvest_test_plan_runs',
    ];
    foreach ($storageTypes as $storageId) {
      $this->assertEquals(0, count($this->getStorageFactory()->getInstance($storageId)->retrieveAll()));
    }

    // Deregister harvest.
    $service->deregisterHarvest('test_plan');
    $this->assertEquals(0, count($this->getStorageFactory()->getInstance('harvest_plans')->retrieveAll()));
  }

  /**
   *
   */
  public function testGetHarvestPlan() {
    $storeFactory = (new Chain($this))
      ->add(DatabaseTableFactory::class, "getInstance", DatabaseTable::class)
      ->add(DatabaseTable::class, "retrieve", "Hello")
      ->getMock();

    $service = new HarvestService($storeFactory);
    $plan = $service->getHarvestPlan("test");
    $this->assertEquals("Hello", $plan);
  }

  /**
   *
   */
  public function testGetHarvestRunInfo() {
    $storeFactory = (new Chain($this))
      ->add(DatabaseTableFactory::class, "getInstance", DatabaseTable::class)
      ->add(DatabaseTable::class, "retrieveAll", ["Hello"])
      ->add(DatabaseTable::class, "retrieve", "Hello")
      ->add(DatabaseTable::class, "store", "Hello")
      ->getMock();

    $dkanHarvester = (new Chain($this))
      ->add(HarvestService::class, "harvest", "Hello")
      ->getMock();

    $service = $this->getMockBuilder(HarvestService::class)
      ->setConstructorArgs([$storeFactory])
      ->setMethods(['getDkanHarvesterInstance'])
      ->getMock();

    $service->method('getDkanHarvesterInstance')->willReturn($dkanHarvester);

    $result = $service->getHarvestRunInfo("test", "1");
    $this->assertFalse($result);
  }

  /**
   *
   */
  private function getStorageFactory() {
    if (!isset($this->storageFactory)) {
      $this->storageFactory = new class() implements FactoryInterface {
        private $stores = [];

        /**
         *
         */
        public function getInstance(string $identifier, array $config = []) {
          if (!isset($this->stores[$identifier])) {
            $this->stores[$identifier] = new class() extends Memory {

              /**
               *
               */
              public function retrieveAll(): array {
                return array_keys(parent::retrieveAll());
              }

              /**
               *
               */
              public function destroy() {
                $this->storage = [];
              }

            };
          }
          return $this->stores[$identifier];
        }

      };
    }

    return $this->storageFactory;
  }

}
