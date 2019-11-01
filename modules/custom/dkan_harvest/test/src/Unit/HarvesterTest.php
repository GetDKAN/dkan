<?php

use Harvest\Harvester;
use Drupal\dkan_common\Tests\Mock\Chain;
use Drupal\dkan_harvest\Storage\DatabaseTableFactory;
use PHPUnit\Framework\TestCase;
use Drupal\dkan_harvest\Storage\DatabaseTable;
use Drupal\dkan_harvest\Harvester as HarvestService;

/**
 *
 */
class HarvesterTest extends TestCase {

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
  public function testDeregisterHarvest() {
    $storeFactory = (new Chain($this))
      ->add(DatabaseTableFactory::class, "getInstance", DatabaseTable::class)
      ->add(DatabaseTable::class, "retrieve", "Hello")
      ->add(DatabaseTable::class, "destroy", null)
      ->add(DatabaseTable::class, "remove", "Hello")
      ->getMock();

    $dkanHarvester = (new Chain($this))
      ->add(Harvester::class, "revert", NULL)
      ->getMock();

    $service = $this->getMockBuilder(HarvestService::class)
      ->setConstructorArgs([$storeFactory])
      ->setMethods(['getDkanHarvesterInstance'])
      ->getMock();

    $service->method('getDkanHarvesterInstance')->willReturn($dkanHarvester);

    $result = $service->deregisterHarvest("test");
    $this->assertEquals("Hello", $result);
  }

  /**
   *
   */
  public function testRunHarvest() {
    $storeFactory = (new Chain($this))
      ->add(DatabaseTableFactory::class, "getInstance", DatabaseTable::class)
      ->add(DatabaseTable::class, "retrieveAll", ["Hello"])
      ->add(DatabaseTable::class, "retrieve", "Hello")
      ->add(DatabaseTable::class, "store", "Hello")
      ->getMock();

    $dkanHarvester = (new Chain($this))
      ->add(Harvester::class, "harvest", "Hello")
      ->getMock();

    $service = $this->getMockBuilder(HarvestService::class)
      ->setConstructorArgs([$storeFactory])
      ->setMethods(['getDkanHarvesterInstance'])
      ->getMock();

    $service->method('getDkanHarvesterInstance')->willReturn($dkanHarvester);

    $result = $service->runHarvest("test");
    $this->assertEquals("Hello", $result);
  }

  /**
   *
   */
  public function testGetAllHarvestRunInfo() {
    $storeFactory = (new Chain($this))
      ->add(DatabaseTableFactory::class, "getInstance", DatabaseTable::class)
      ->add(DatabaseTable::class, "retrieveAll", ["Hello"])
      ->add(DatabaseTable::class, "retrieve", "Hello")
      ->add(DatabaseTable::class, "store", "Hello")
      ->getMock();

    $dkanHarvester = (new Chain($this))
      ->add(Harvester::class, "harvest", "Hello")
      ->getMock();

    $service = $this->getMockBuilder(HarvestService::class)
      ->setConstructorArgs([$storeFactory])
      ->setMethods(['getDkanHarvesterInstance'])
      ->getMock();

    $service->method('getDkanHarvesterInstance')->willReturn($dkanHarvester);

    $result = $service->getAllHarvestRunInfo("test");
    $this->assertTrue(is_array($result));
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

}
