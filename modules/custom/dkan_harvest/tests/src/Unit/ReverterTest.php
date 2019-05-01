<?php

namespace Drupal\Tests\dkan_harvest\Unit;

use Drupal\dkan_common\Tests\DkanTestBase;
use Drupal\dkan_harvest\Reverter;
use Harvest\Storage\Storage;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Drupal\dkan_api\Storage\DrupalNodeDataset;

/**
 * Tests Drupal\dkan_harvest\Reverter.
 *
 * @coversDefaultClass Drupal\dkan_harvest\Reverter
 * @group dkan_harvest
 */
class HarvesterTest extends DkanTestBase {

  /**
   * Tests __construct().
   */
  public function testConstruct() {

    $mock = $this->getMockBuilder(Reverter::class)
      ->disableOriginalConstructor()
      ->setMethods(NULL)
      ->getMock();

    $mockStorage = $this->createMock(Storage::class);

    $dummySourceId = 42;

    // Assert.
    $mock->__construct($dummySourceId, $mockStorage);

    $this->assertEquals($dummySourceId, $this->readAttribute($mock, 'sourceId'));
    $this->assertSame($mockStorage, $this->readAttribute($mock, 'hashStorage'));
  }

  /**
   * Tests run().
   */
  public function testRun() {

    $mock = $this->getMockBuilder(Reverter::class)
      ->disableOriginalConstructor()
      ->setMethods([
        'log',
      ])
      ->getMock();

    $mockHashStorage = $this->getMockBuilder(Storage::class)
      ->setMethods([
        'retrieveAll',
        'remove',
      ])
      ->getMockForAbstractClass();

    $mockDrupalNodeDataset = $this->getMockBuilder(DrupalNodeDataset::class)
      ->setMethods(['remove'])
      ->disableOriginalConstructor()
      ->getMock();

    $dummyContainer = new ContainerBuilder();

    $dummyContainer->set('dkan_api.storage.drupal_node_dataset', $mockDrupalNodeDataset);

    \Drupal::setContainer($dummyContainer);

    $sourceId = 43;
    $mock->sourceId = $sourceId;
    $this->writeProtectedProperty($mock, 'hashStorage', $mockHashStorage);

    $allRetrieved = [
      'foo' => 'bar',
      'foo1' => 'bar1',
    ];

    $uuids = array_keys($allRetrieved);

    $expectedCount = count($uuids);

    // Expects.
    $mock->expects($this->once())
      ->method('log')
      ->with('DEBUG', 'revert', 'Reverting harvest ' . $sourceId);

    $mockHashStorage->expects($this->once())
      ->method('retrieveAll')
      ->willReturn($allRetrieved);

    $mockHashStorage->expects($this->exactly($expectedCount))
      ->method('remove')
      ->withConsecutive(
                    [$uuids[0]],
                    [$uuids[1]]
    );

    $mockDrupalNodeDataset->expects($this->exactly($expectedCount))
      ->method('remove')
      ->withConsecutive(
                    [$uuids[0]],
                    [$uuids[1]]
    );

    // Assert.
    $actual = $mock->run();
    $this->assertEquals($expectedCount, $actual);
  }

}
