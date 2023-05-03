<?php

namespace Drupal\Tests\harvest\Unit;

use Contracts\FactoryInterface;
use Contracts\Mock\Storage\Memory;
use Drupal\Component\DependencyInjection\Container;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Tests\common\Traits\ServiceCheckTrait;
use Drupal\datastore\Storage\DatabaseTable;
use Drupal\harvest\HarvestService;
use Drupal\harvest\Storage\DatabaseTableFactory;
use Drupal\metastore\MetastoreService;
use Drupal\node\NodeStorage;
use Harvest\ETL\Extract\DataJson;
use Harvest\ETL\Load\Simple;
use MockChain\Chain;
use MockChain\Options;
use MockChain\Sequence;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\harvest\HarvestService
 * @group harvest
 */
class ServiceTest extends TestCase {
  use ServiceCheckTrait;

  private $storageFactory;

  /**
   *
   */
  public function test() {
    $options = (new Options())
      ->add('dkan.harvest.storage.database_table', $this->getStorageFactory())
      ->add('dkan.metastore.service', $this->getMetastoreMockChain())
      ->add('entity_type.manager', $this->getEntityTypeManagerMockChain())
      ->index(0);

    $this->checkService('dkan.harvest.storage.database_table', 'harvest');

    $container = (new Chain($this))
      ->add(ContainerInterface::class, 'get', $options)
      ->getMock();

    $service = HarvestService::create($container);

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

    // Run harvest again, no changes.
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

    $service = new HarvestService($storeFactory, $this->getMetastoreMockChain(), $this->getEntityTypeManagerMockChain());
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
      ->add(HarvestService::class)
      ->getMock();

    $service = $this->getMockBuilder(HarvestService::class)
      ->setConstructorArgs([$storeFactory, $this->getMetastoreMockChain(), $this->getEntityTypeManagerMockChain()])
      ->onlyMethods(['getDkanHarvesterInstance'])
      ->getMock();

    $service->method('getDkanHarvesterInstance')->willReturn($dkanHarvester);

    $result = $service->getHarvestRunInfo("test", "1");
    $this->assertFalse($result);
  }

  /**
   * Private.
   */
  private function getStorageFactory() {
    if (!isset($this->storageFactory)) {
      $this->storageFactory = new class() implements FactoryInterface {
        private $stores = [];

        /**
         * Getter.
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
              public function destruct() {
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

  /**
   * Private.
   */
  private function getMetastoreMockChain() {
    return (new Chain($this))
      ->add(MetastoreService::class, 'publish', '1')
      ->getMock();
  }

  /**
   * Private.
   */
  private function getEntityTypeManagerMockChain() {
    return (new Chain($this))
      ->add(EntityTypeManager::class, 'getStorage', NodeStorage::class)
      ->getMock();
  }

  /**
   *
   */
  public function testPublish() {

    $datasetUuids = ['abcd-1001', 'abcd-1002', 'abcd-1003', 'abcd-1004'];
    $lastRunInfo = (new Sequence())
      ->add(json_encode((object) [
        'status' => [
          'extracted_items_ids' => $datasetUuids,
          'load' => [
            'abcd-1001' => "SUCCESS",
            'abcd-1002' => "SUCCESS",
            'abcd-1003' => "SUCCESS",
            'abcd-1004' => "FAILURE",
          ],
        ],
      ]))
      ->add(json_encode((object) [
        'status' => [],
      ]));

    $metastorePublicationResults = (new Sequence())
      // abcd-1001 will be skipped since already published.
      ->add(FALSE)
      // abcd-1002 will be skipped due to exception.
      ->add(new \Exception('FooBar'))
      // abcd-1003 should be published without issue.
      ->add(TRUE);

    $logger = (new Chain($this))
      ->add(LoggerChannelFactory::class, 'get', LoggerChannelInterface::class)
      ->add(LoggerChannelInterface::class, 'error', NULL, 'error');

    $container = $this->getCommonMockChain()
      ->add(DatabaseTable::class, "retrieve", $lastRunInfo)
      ->add(MetastoreService::class, 'publish', $metastorePublicationResults);

    $service = HarvestService::create($container->getMock());
    $service->setLoggerFactory($logger->getMock());
    $result = $service->publish('1');

    $this->assertEquals(['abcd-1003'], $result);

    $loggerResult = $logger->getStoredInput('error')[0];
    $error = 'Error applying method publish to dataset abcd-1002: FooBar';
    $this->assertEquals($error, $loggerResult);

    $result = $service->publish('2');
    $this->assertEmpty($result);
  }

  public function testArchive() {
    $datasetUuids = ['abcd-1001', 'abcd-1002', 'abcd-1003', 'abcd-1004'];
    $lastRunInfo = (new Sequence())
      ->add(json_encode((object) [
        'status' => [
          'extracted_items_ids' => $datasetUuids,
          'load' => [
            'abcd-1001' => "SUCCESS",
            'abcd-1002' => "SUCCESS",
            'abcd-1003' => "SUCCESS",
            'abcd-1004' => "FAILURE",
          ],
        ],
      ]))
      ->add(json_encode((object) [
        'status' => [],
      ]));

    $metastoreArchiveResults = (new Sequence())
      // abcd-1001 will be skipped since already archived.
      ->add(FALSE)
      // abcd-1002 will be skipped due to exception.
      ->add(new \Exception('FooBar'))
      // abcd-1003 should be archived without issue.
      ->add(TRUE);

    $logger = (new Chain($this))
      ->add(LoggerChannelFactory::class, 'get', LoggerChannelInterface::class)
      ->add(LoggerChannelInterface::class, 'error', NULL, 'error');

    $container = $this->getCommonMockChain()
      ->add(DatabaseTable::class, "retrieve", $lastRunInfo)
      ->add(MetastoreService::class, 'archive', $metastoreArchiveResults);

    $service = HarvestService::create($container->getMock());
    $service->setLoggerFactory($logger->getMock());
    $result = $service->archive('1');

    $this->assertEquals(['abcd-1003'], $result);

    $loggerResult = $logger->getStoredInput('error')[0];
    $error = 'Error applying method archive to dataset abcd-1002: FooBar';
    $this->assertEquals($error, $loggerResult);

    $result = $service->archive('2');
    $this->assertEmpty($result);
  }

  /**
   *
   */
  public function testGetOrphansFromCompleteHarvest() {

    $successiveExtractedIds = (new Options())
      ->add('101', json_encode((object) ['status' => ['extracted_items_ids' => [1, 2, 3]]]))
      ->add('102', json_encode((object) ['status' => ['extracted_items_ids' => [1, 2, 4]]]))
      ->add('103', json_encode((object) ['status' => ['extracted_items_ids' => [1, 3]]]))
      ->add('104', json_encode((object) ['status' => ['extracted_items_ids' => [1, 2, 3]]]))
      ->use('extractedIds');

    $container = $this->getCommonMockChain()
      ->add(DatabaseTable::class, 'retrieveAll', ['101', '102', '103', '104'])
      ->add(DatabaseTable::class, 'retrieve', $successiveExtractedIds);
    $service = HarvestService::create($container->getMock());
    $removedIds = $service->getOrphanIdsFromCompleteHarvest('1');

    $this->assertEquals(['4'], array_values($removedIds));
  }

  public function testGetAllHarvestIds() {
    $container = $this->getCommonMockChain();

    $service = HarvestService::create($container->getMock());

    $expected = ['100', '102', '101'];

    $actual = $service->getAllHarvestIds();
    $this->assertEquals($expected, $actual);
  }

  /**
   * Private.
   */
  private function getCommonMockChain() {

    $options = (new Options())
      ->add('dkan.harvest.storage.database_table', DatabaseTableFactory::class)
      ->add('dkan.metastore.service', MetastoreService::class)
      ->add('entity_type.manager', EntityTypeManager::class)
      ->index(0);

    return (new Chain($this))
      ->add(Container::class, 'get', $options)
      // DatabaseTableFactory.
      ->add(DatabaseTableFactory::class, "getInstance", DatabaseTable::class)
      ->add(DatabaseTable::class, "retrieveAll", ['100', '102', '101'])
      // Metastore.
      ->add(MetastoreService::class, 'publish', '1');
  }

}
