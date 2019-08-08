<?php

namespace Drupal\Tests\dkan_harvest\Unit\Service;

use Drupal\dkan_common\Tests\DkanTestBase;
use Drupal\dkan_harvest\Service\Harvest;
use Drupal\dkan_harvest\Load\IFileHelper;
use Drupal\dkan_harvest\Storage\File;
use Harvest\ETL\Factory as EtlFactory;
use Harvest\Harvester;
use Harvest\ResultInterpreter;
use Harvest\Storage\Storage;
use Drupal\dkan_harvest\Service\Factory as HarvestFactory;
use Drupal\dkan_common\Service\JsonUtil;
use Drupal\Component\Datetime\TimeInterface;

/**
 * Tests DDrupal\dkan_harvest\Service\Harvest.
 *
 * @coversDefaultClass Drupal\dkan_harvest\Service\Harvest
 * @group dkan_harvest
 */
class HarvestTest extends DkanTestBase {

  /**
   * Tests __Construct().
   */
  public function testConstruct() {
    // Setup.
    $mock = $this->getMockBuilder(Harvest::class)
      ->setMethods(NULL)
      ->disableOriginalConstructor()
      ->getMock();

    $mockFactory  = $this->createMock(HarvestFactory::class);
    $mockJsonUtil = $this->createMock(JsonUtil::class);
    $mockTime     = $this->createMock(TimeInterface::class);

    // Assert.
    $mock->__construct($mockFactory, $mockJsonUtil, $mockTime);
    $this->assertSame($mockFactory, $this->readAttribute($mock, 'factory'));
    $this->assertSame($mockJsonUtil, $this->readAttribute($mock, 'jsonUtil'));
    $this->assertSame($mockTime, $this->readAttribute($mock, 'time'));
  }

  /**
   * Tests GetAllHarvestIds().
   */
  public function testGetAllHarvestIds() {

    // Setup.
    $mock = $this->getMockBuilder(Harvest::class)
      ->setMethods(NULL)
      ->disableOriginalConstructor()
      ->getMock();

    $mockFactory = $this->getMockBuilder(HarvestFactory::class)
      ->setMethods(['getPlanStorage'])
      ->disableOriginalConstructor()
      ->getMock();
    $this->writeProtectedProperty($mock, 'factory', $mockFactory);

    $mockStorage = $this->getMockBuilder(Storage::class)
      ->setMethods(['retrieveAll'])
      ->disableOriginalConstructor()
      ->getMockForAbstractClass();

    $retrieved = [
      uniqid('aaa') => '',
      uniqid('bbb') => '',
    ];
    $expected  = array_keys($retrieved);

    // Expect.
    $mockFactory->expects($this->once())
      ->method('getPlanStorage')
      ->willReturn($mockStorage);

    $mockStorage->expects($this->once())
      ->method('retrieveAll')
      ->willReturn($retrieved);

    // Assert.
    $actual = $mock->getAllHarvestIds();
    $this->assertEquals($expected, $actual);
  }

  /**
   * Tests RegisterHarvest().
   */
  public function testRegisterHarvest() {

    // Setup.
    $mock = $this->getMockBuilder(Harvest::class)
      ->setMethods(['validateHarvestPlan'])
      ->disableOriginalConstructor()
      ->getMock();

    $mockFactory = $this->getMockBuilder(HarvestFactory::class)
      ->setMethods(['getPlanStorage'])
      ->disableOriginalConstructor()
      ->getMock();
    $this->writeProtectedProperty($mock, 'factory', $mockFactory);

    $mockStorage = $this->getMockBuilder(Storage::class)
      ->setMethods(['store'])
      ->disableOriginalConstructor()
      ->getMockForAbstractClass();

    $identifier  = uniqid('foo');
    $plan        = (object) [
        'identifier' => $identifier
    ];
    $encodedPlan = json_encode($plan);

    // Expect.
    $mock->expects($this->once())
      ->method('validateHarvestPlan')
      ->with($plan)
      ->willReturn(TRUE);

    $mockFactory->expects($this->once())
      ->method('getPlanStorage')
      ->willReturn($mockStorage);

    $mockStorage->expects($this->once())
      ->method('store')
      ->with($encodedPlan, $identifier)
      ->willReturn($identifier);

    // Assert.
    $actual = $mock->registerHarvest($plan);
    $this->assertEquals($identifier, $actual);
  }

  /**
   * Tests DeregisterHarvest().
   */
  public function testDeregisterHarvest() {

    // Setup.
    $mock = $this->getMockBuilder(Harvest::class)
      ->setMethods(['revertHarvest'])
      ->disableOriginalConstructor()
      ->getMock();

    $mockFactory = $this->getMockBuilder(HarvestFactory::class)
      ->setMethods(['getPlanStorage'])
      ->disableOriginalConstructor()
      ->getMock();
    $this->writeProtectedProperty($mock, 'factory', $mockFactory);

    $mockStorage = $this->getMockBuilder(Storage::class)
      ->setMethods(['remove'])
      ->disableOriginalConstructor()
      ->getMockForAbstractClass();

    $id       = uniqid('foo');
    $expected = TRUE;

    // Expect.
    $mock->expects($this->once())
      ->method('revertHarvest')
      ->with($id);

    $mockFactory->expects($this->once())
      ->method('getPlanStorage')
      ->willReturn($mockStorage);

    $mockStorage->expects($this->once())
      ->method('remove')
      ->with($id)
      ->willReturn($expected);

    // Assert.
    $actual = $mock->deregisterHarvest($id);
    $this->assertEquals($expected, $actual);
  }

  /**
   * Tests RevertHarvest().
   */
  public function testRevertHarvest() {

    // Setup.
    $mock = $this->getMockBuilder(Harvest::class)
      ->setMethods(NULL)
      ->disableOriginalConstructor()
      ->getMock();

    $mockFactory = $this->getMockBuilder(HarvestFactory::class)
      ->setMethods(['getHarvester'])
      ->disableOriginalConstructor()
      ->getMock();
    $this->writeProtectedProperty($mock, 'factory', $mockFactory);

    $mockHarvester = $this->getMockBuilder(Harvester::class)
      ->setMethods(['revert'])
      ->disableOriginalConstructor()
      ->getMock();

    $id       = uniqid('foo');
    $expected = TRUE;
    // Expect.
    $mockFactory->expects($this->once())
      ->method('getHarvester')
      ->with($id)
      ->willReturn($mockHarvester);

    $mockHarvester->expects($this->once())
      ->method('revert')
      ->willReturn($expected);

    // Assert.
    $actual = $mock->revertHarvest($id);
    $this->assertEquals($expected, $actual);
  }

  /**
   * Tests RunHarvest().
   */
  public function testRunHarvest() {

    // Setup.
    $mock = $this->getMockBuilder(Harvest::class)
      ->setMethods(NULL)
      ->disableOriginalConstructor()
      ->getMock();

    $mockFactory = $this->getMockBuilder(HarvestFactory::class)
      ->setMethods([
        'getHarvester',
        'getStorage',
      ])
      ->disableOriginalConstructor()
      ->getMock();
    $this->writeProtectedProperty($mock, 'factory', $mockFactory);

    $mockHarvester = $this->getMockBuilder(Harvester::class)
      ->setMethods(['harvest'])
      ->disableOriginalConstructor()
      ->getMock();

    $mockStorage = $this->getMockBuilder(Storage::class)
      ->setMethods(['store'])
      ->disableOriginalConstructor()
      ->getMockForAbstractClass();

    $mockTime = $this->getMockBuilder(TimeInterface::class)
      ->setMethods(['getCurrentTime'])
      ->disableOriginalConstructor()
      ->getMockForAbstractClass();
    $this->writeProtectedProperty($mock, 'time', $mockTime);

    $id              = uniqid('foo');
    $currentTime     = 123456;
    $expected        = TRUE;
    $encodedExpected = json_encode($expected);

    // Expect.
    $mockFactory->expects($this->once())
      ->method('getHarvester')
      ->with($id)
      ->willReturn($mockHarvester);

    $mockHarvester->expects($this->once())
      ->method('harvest')
      ->willReturn($expected);

    $mockFactory->expects($this->once())
      ->method('getStorage')
      ->with($id, 'run')
      ->willReturn($mockStorage);

    $mockTime->expects($this->once())
      ->method('getCurrentTime')
      ->willReturn($currentTime);

    $mockStorage->expects($this->once())
      ->method('store')
      ->with($encodedExpected, $currentTime);

    // Assert.
    $actual = $mock->runHarvest($id);
    $this->assertEquals($expected, $actual);
  }

  /**
   * Data provider for testGetHarvestRunInfo.
   *
   * @return array
   *   Array of arguments.
   */
  public function dataGetHarvestRunInfo() {

    return [
      [
        'validRunId',
        [
          'validRunId' => 'foo',
        ],
        'foo'
      ],
      [
        'invalidRunId',
        [],
        FALSE
      ],
    ];
  }

  /**
   * Tests getHarvestRunInfo().
   *
   * @dataProvider dataGetHarvestRunInfo
   */
  public function testGetHarvestRunInfo(string $runId, array $allRuns, $expected) {

    // Setup.
    $mock = $this->getMockBuilder(Harvest::class)
      ->setMethods(['getAllHarvestRunInfo'])
      ->disableOriginalConstructor()
      ->getMock();

    $id = 'foobar';

    // Expect.
    $mock->expects($this->once())
      ->method('getAllHarvestRunInfo')
      ->with($id)
      ->willReturn($allRuns);

    // Assert.
    $actual = $mock->getHarvestRunInfo($id, $runId);
    $this->assertEquals($expected, $actual);
  }

  /**
   * Tests getAllHarvestRunInfo().
   */
  public function testgetAllHarvestRunInfo() {

    // Setup.
    $mock = $this->getMockBuilder(Harvest::class)
      ->setMethods(NULL)
      ->disableOriginalConstructor()
      ->getMock();

    $mockJsonUtil = $this->getMockBuilder(JsonUtil::class)
      ->setMethods(['decodeArrayOfJson'])
      ->disableOriginalConstructor()
      ->getMock();
    $this->writeProtectedProperty($mock, 'jsonUtil', $mockJsonUtil);

    $mockFactory = $this->getMockBuilder(HarvestFactory::class)
      ->setMethods([
        'getStorage'
      ])
      ->disableOriginalConstructor()
      ->getMock();
    $this->writeProtectedProperty($mock, 'factory', $mockFactory);

    $mockStorage = $this->getMockBuilder(Storage::class)
      ->setMethods(['retrieveAll'])
      ->disableOriginalConstructor()
      ->getMockForAbstractClass();

    $id        = uniqid('foo');
    $retrieved = ['{some json like string validity not important}'];
    $expected  = ['same deal as above'];

    // Expect.
    $mockFactory->expects($this->once())
      ->method('getStorage')
      ->with($id, 'run')
      ->willReturn($mockStorage);

    $mockStorage->expects($this->once())
      ->method('retrieveAll')
      ->willReturn($retrieved);

    $mockJsonUtil->expects($this->once())
      ->method('decodeArrayOfJson')
      ->with($retrieved)
      ->willReturn($expected);

    // Assert.
    $actual = $mock->getAllHarvestRunInfo($id);
    $this->assertEquals($expected, $actual);
  }

}
