<?php

namespace Drupal\Tests\harvest\Unit;

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
use MockChain\Chain;
use MockChain\Options;
use MockChain\Sequence;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Drupal\harvest\HarvestService
 * @coversDefaultClass \Drupal\harvest\HarvestService
 *
 * @group dkan
 * @group harvest
 * @group unit
 */
class HarvestServiceTest extends TestCase {
  use ServiceCheckTrait;

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
