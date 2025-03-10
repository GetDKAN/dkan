<?php

declare(strict_types=1);

namespace Drupal\Tests\harvest\Unit;

use Drupal\Component\DependencyInjection\Container;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\datastore\Storage\DatabaseTable;
use Drupal\harvest\Entity\HarvestPlanRepository;
use Drupal\harvest\Entity\HarvestRunRepository;
use Drupal\harvest\HarvestService;
use Drupal\harvest\Storage\DatabaseTableFactory;
use Drupal\harvest\Storage\HarvestHashesDatabaseTableFactory;
use Drupal\metastore\MetastoreService;
use Drupal\Tests\common\Traits\ServiceCheckTrait;
use MockChain\Chain;
use MockChain\Options;
use MockChain\Sequence;
use Drupal\harvest\Harvester;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @covers \Drupal\harvest\HarvestService
 * @coversDefaultClass \Drupal\harvest\HarvestService
 *
 * @group dkan
 * @group harvest
 * @group unit
 *
 * @see \Drupal\Tests\harvest\Kernel\HarvestServiceTest
 */
class HarvestServiceTest extends TestCase {
  use ServiceCheckTrait;

  public function testGetHarvestPlan() {
    $planRepository = (new Chain($this))
      ->add(HarvestPlanRepository::class, 'getPlanJson', 'Hello')
      ->getMock();

    $service = new HarvestService(
      $this->createStub(DatabaseTableFactory::class),
      $this->createStub(HarvestHashesDatabaseTableFactory::class),
      $this->createStub(MetastoreService::class),
      $planRepository,
      $this->createStub(HarvestRunRepository::class),
      $this->createStub(LoggerInterface::class)
    );
    $plan = $service->getHarvestPlan('test');
    $this->assertEquals('Hello', $plan);
  }

  public function testGetHarvestRunInfo() {
    $storeFactory = (new Chain($this))
      ->add(DatabaseTableFactory::class, 'getInstance', DatabaseTable::class)
      ->add(DatabaseTable::class, 'retrieveAll', ['Hello'])
      ->add(DatabaseTable::class, 'retrieve', 'Hello')
      ->add(DatabaseTable::class, 'store', 'Hello')
      ->getMock();

    $service = $this->getMockBuilder(HarvestService::class)
      ->setConstructorArgs([
        $storeFactory,
        $this->createStub(HarvestHashesDatabaseTableFactory::class),
        $this->getMetastoreMockChain(),
        $this->createStub(HarvestPlanRepository::class),
        $this->createStub(HarvestRunRepository::class),
        $this->createStub(LoggerInterface::class),
      ])
      ->onlyMethods(['getDkanHarvesterInstance'])
      ->getMock();

    $service->method('getDkanHarvesterInstance')
      ->willReturn($this->createStub(Harvester::class));

    $result = $service->getHarvestRunInfo('test', '1');
    $this->assertFalse($result);
  }

  private function getMetastoreMockChain() {
    return (new Chain($this))
      ->add(MetastoreService::class, 'publish', '1')
      ->getMock();
  }

  public function testPublish() {
    $datasetUuids = ['abcd-1001', 'abcd-1002', 'abcd-1003', 'abcd-1004'];
    $lastRunInfo = (new Sequence())
      ->add(json_encode((object) [
        'status' => [
          'extracted_items_ids' => $datasetUuids,
          'load' => [
            'abcd-1001' => 'SUCCESS',
            'abcd-1002' => 'SUCCESS',
            'abcd-1003' => 'SUCCESS',
            'abcd-1004' => 'FAILURE',
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
      ->add(LoggerInterface::class, 'error', NULL, 'error');

    $container = $this->getCommonMockChain($logger->getMock())
      ->add(HarvestRunRepository::class, 'retrieveRunJson', $lastRunInfo)
      ->add(MetastoreService::class, 'publish', $metastorePublicationResults);

    $service = HarvestService::create($container->getMock());
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
            'abcd-1001' => 'SUCCESS',
            'abcd-1002' => 'SUCCESS',
            'abcd-1003' => 'SUCCESS',
            'abcd-1004' => 'FAILURE',
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
      ->add(LoggerInterface::class, 'error', NULL, 'error');

    $container = $this->getCommonMockChain($logger->getMock())
      ->add(HarvestRunRepository::class, 'retrieveRunJson', $lastRunInfo)
      ->add(MetastoreService::class, 'archive', $metastoreArchiveResults);

    $service = HarvestService::create($container->getMock());
    $result = $service->archive('1');

    $this->assertEquals(['abcd-1003'], $result);

    $loggerResult = $logger->getStoredInput('error')[0];
    $error = 'Error applying method archive to dataset abcd-1002: FooBar';
    $this->assertEquals($error, $loggerResult);

    $result = $service->archive('2');
    $this->assertEmpty($result);
  }

  public function testGetOrphansFromCompleteHarvest() {
    $successiveExtractedIds = (new Options())
      ->add(['1', '101'], [1, 2, 3])
      ->add(['1', '102'], [1, 2, 4])
      ->add(['1', '103'], [1, 3])
      ->add(['1', '104'], [1, 2, 3])
      ->use('extractedIds');

    $container = $this->getCommonMockChain()
      ->add(HarvestRunRepository::class, 'retrieveAllRunIds', ['101', '102', '103', '104'])
      ->add(HarvestRunRepository::class, 'getExtractedUuids', $successiveExtractedIds);
    $service = HarvestService::create($container->getMock());
    $removedIds = $service->getOrphanIdsFromCompleteHarvest('1');

    $this->assertEquals(['4'], array_values($removedIds));
  }

  private function getCommonMockChain($logger = NULL) {

    $options = (new Options())
      ->add('dkan.harvest.storage.database_table', DatabaseTableFactory::class)
      ->add('dkan.harvest.storage.hashes_database_table', HarvestHashesDatabaseTableFactory::class)
      ->add('dkan.metastore.service', MetastoreService::class)
      ->add('entity_type.manager', EntityTypeManager::class)
      ->add('dkan.harvest.harvest_plan_repository', HarvestPlanRepository::class)
      ->add('dkan.harvest.storage.harvest_run_repository', HarvestRunRepository::class);

    if ($logger) {
      $options->add('dkan.harvest.logger_channel', $logger)
        ->index(0);
    }
    else {
      $options->add('dkan.harvest.logger_channel', LoggerInterface::class)
        ->index(0);
    }

    return (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(HarvestRunRepository::class, 'retrieveAllRunIds', ['100', '102', '101'])
      ->add(MetastoreService::class, 'publish', '1');
  }

}
