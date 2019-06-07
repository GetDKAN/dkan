<?php

namespace Drupal\Tests\dkan_datastore\Unit\Manager;

use Drupal\dkan_datastore\Manager\DatastoreManagerBuilder;
use Drupal\dkan_common\Tests\DkanTestBase;
use Dkan\Datastore\Manager\IManager;
use Dkan\Datastore\Resource;
use Dkan\Datastore\Manager\InfoProvider;
use Dkan\Datastore\Manager\Info;
use Dkan\Datastore\Manager\SimpleImport\SimpleImport;
use Dkan\Datastore\LockableBinStorage;
use Dkan\Datastore\Manager\Factory as DatastoreManagerFactory;
use Dkan\Datastore\Locker;
use Drupal\dkan_datastore\Storage\Database;
use Drupal\dkan_datastore\Manager\DatastoreManagerBuilderHelper as Helper;
use Dkan\Datastore\Storage\IKeyValue;

/**
 * @coversDefaultClass Drupal\dkan_datastore\Manager\DatastoreManagerBuilder
 * @group dkan_datastore
 */
class DatastoreManagerBuilderTest extends DkanTestBase {

  /**
   * Tests __construct().
   */
  public function testConstruct() {
    // Setup.
    $mock = $this->getMockBuilder(DatastoreManagerBuilder::class)
      ->setMethods(NULL)
      ->disableOriginalConstructor()
      ->getMock();

    $mockContainer = $this->getMockContainer();
    $mockHelper    = $this->createMock(Helper::class);

    // Expect.
    // Nothing is fetch at construct.
    $mockContainer->expects($this->once())
      ->method('get')
      ->with('dkan_datastore.manager.datastore_manager_builder_helper')
      ->willReturn($mockHelper);

    // Assert.
    $mock->__construct($mockContainer);
    $this->assertSame(
      $mockContainer,
      $this->readAttribute($mock, 'container')
    );
    $this->assertSame(
      $mockHelper,
      $this->readAttribute($mock, 'helper')
    );
  }

  /**
   * Tests GetInfo().
   */
  public function testGetInfo() {
    // Setup.
    $mock = $this->getMockBuilder(DatastoreManagerBuilder::class)
      ->setMethods(NULL)
      ->disableOriginalConstructor()
      ->getMock();

    $mockHelper = $this->getMockBuilder(Helper::class)
      ->setMethods(['newInfo'])
      ->disableOriginalConstructor()
      ->getMock();
    $this->writeProtectedProperty($mock, 'helper', $mockHelper);

    $mockInfo = $this->createMock(Info::class);

    // Expect.
    $mockHelper->expects($this->once())
      ->method('newInfo')
      ->with(SimpleImport::class, "simple_import", "SimpleImport")
      ->willReturn($mockInfo);

    // Assert.
    $actual = $this->invokeProtectedMethod($mock, 'getInfo');
    $this->assertSame($mockInfo, $actual);
  }

  /**
   * Tests GetInfoProvider().
   */
  public function testGetInfoProvider() {
    // Setup.
    $mock = $this->getMockBuilder(DatastoreManagerBuilder::class)
      ->setMethods(['getInfo'])
      ->disableOriginalConstructor()
      ->getMock();

    $mockHelper = $this->getMockBuilder(Helper::class)
      ->setMethods(['newInfoProvider'])
      ->disableOriginalConstructor()
      ->getMock();
    $this->writeProtectedProperty($mock, 'helper', $mockHelper);

    $mockInfo         = $this->createMock(Info::class);
    $mockInfoProvider = $this->getMockBuilder(InfoProvider::class)
      ->setMethods(['addInfo'])
      ->disableOriginalConstructor()
      ->getMock();

    // Expect.
    $mockHelper->expects($this->once())
      ->method('newInfoProvider')
      ->willReturn($mockInfoProvider);

    $mock->expects($this->once())
      ->method('getInfo')
      ->willReturn($mockInfo);

    $mockInfoProvider->expects($this->once())
      ->method('addInfo')
      ->with($mockInfo);

    // Assert.
    $actual = $this->invokeProtectedMethod($mock, 'getInfoProvider');
    $this->assertSame($mockInfoProvider, $actual);
  }

  /**
   * Tests setResourceFromFilePath().
   */
  public function testSetResourceFromFilePath() {
    // Setup.
    $mock = $this->getMockBuilder(DatastoreManagerBuilder::class)
      ->setMethods(['setResource'])
      ->disableOriginalConstructor()
      ->getMock();

    $mockHelper = $this->getMockBuilder(Helper::class)
      ->setMethods(['newResourceFromFilePath'])
      ->disableOriginalConstructor()
      ->getMock();
    $this->writeProtectedProperty($mock, 'helper', $mockHelper);

    $mockResource = $this->createMock(Resource::class);
    $id           = uniqid('id');
    $filePath     = uniqid('file-path');

    // Expect.
    $mockHelper->expects($this->once())
      ->method('newResourceFromFilePath')
      ->with($id, $filePath)
      ->willReturn($mockResource);

    $mock->expects($this->once())
      ->method('setResource')
      ->with($mockResource);

    // Assert.
    $actual = $this->invokeProtectedMethod($mock, 'setResourceFromFilePath', $id, $filePath);
    $this->assertSame($mock, $actual);
  }

  /**
   * Tests SetResource().
   */
  public function testSetResource() {
    // Setup.
    $mock = $this->getMockBuilder(DatastoreManagerBuilder::class)
      ->setMethods(NULL)
      ->disableOriginalConstructor()
      ->getMock();

    $mockResource = $this->createMock(Resource::class);

    // Assert.
    $mock->setResource($mockResource);
    $this->assertSame($mockResource, $this->readAttribute($mock, 'resource'));
  }

  /**
   * Tests GetResource().
   */
  public function testGetResource() {
    // Setup.
    $mock = $this->getMockBuilder(DatastoreManagerBuilder::class)
      ->setMethods(NULL)
      ->disableOriginalConstructor()
      ->getMock();

    $mockResource = $this->createMock(Resource::class);
    $this->writeProtectedProperty($mock, 'resource', $mockResource);
    // Assert.
    $actual = $this->invokeProtectedMethod($mock, 'getResource');
    $this->assertSame($mockResource, $actual);
  }

  /**
   * Tests getLockableStorage().
   */
  public function testGetLockableStorage() {
    // Setup.
    $mock = $this->getMockBuilder(DatastoreManagerBuilder::class)
      ->setMethods(NULL)
      ->disableOriginalConstructor()
      ->getMock();

    $mockHelper = $this->getMockBuilder(Helper::class)
      ->setMethods([
        'newLockableStorage',
        'newLocker',
      ])
      ->disableOriginalConstructor()
      ->getMock();
    $this->writeProtectedProperty($mock, 'helper', $mockHelper);

    $mockContainer = $this->getMockContainer();
    $this->writeProtectedProperty($mock, 'container', $mockContainer);

    $mockLocker          = $this->createMock(Locker::class);
    $mockKeyValueStore   = $this->createMock(IKeyValue::class);
    $mockLockableStorage = $this->createMock(LockableBinStorage::class);
    $name                = 'dkan_datastore';

    // Expect.
    $mockHelper->expects($this->once())
      ->method('newLocker')
      ->with($name)
      ->willReturn($mockLocker);

    $mockContainer->expects($this->once())
      ->method('get')
      ->with('dkan_datastore.storage.variable')
      ->willReturn($mockKeyValueStore);

    $mockHelper->expects($this->once())
      ->method('newLockableStorage')
      ->with($name, $mockLocker, $mockKeyValueStore)
      ->willReturn($mockLockableStorage);
    // Assert.
    $actual = $this->invokeProtectedMethod($mock, 'getLockableStorage');
    $this->assertSame($mockLockableStorage, $actual);
  }

  /**
   * Tests GetDatabase().
   */
  public function testGetDatabase() {
    // Setup.
    $mock = $this->getMockBuilder(DatastoreManagerBuilder::class)
      ->setMethods(NULL)
      ->disableOriginalConstructor()
      ->getMock();

    $mockContainer = $this->getMockContainer();
    $this->writeProtectedProperty($mock, 'container', $mockContainer);

    $mockDatabase = $this->createMock(Database::class);

    // Expect.
    $mockContainer->expects($this->once())
      ->method('get')
      ->with('dkan_datastore.database')
      ->willReturn($mockDatabase);

    // Assert.
    $actual = $this->invokeProtectedMethod($mock, 'getDatabase');
    $this->assertSame($mockDatabase, $actual);
  }

  /**
   * Tests BuildFromUuid().
   */
  public function testBuildFromUuid() {
    // Setup.
    $mock = $this->getMockBuilder(DatastoreManagerBuilder::class)
      ->setMethods([
        'setResource',
        'build',
      ])
      ->disableOriginalConstructor()
      ->getMock();

    $mockHelper = $this->getMockBuilder(Helper::class)
      ->setMethods([
        'newResourceFromEntity',
      ])
      ->disableOriginalConstructor()
      ->getMock();
    $this->writeProtectedProperty($mock, 'helper', $mockHelper);

    $uuid = uniqid('foo-uuid');

    $mockResource = $this->createMock(Resource::class);
    $expected     = $this->createMock(IManager::class);

    // Expect.
    $mockHelper->expects($this->once())
      ->method('newResourceFromEntity')
      ->with($uuid)
      ->willReturn($mockResource);

    $mock->expects($this->once())
      ->method('setResource')
      ->with($mockResource);

    $mock->expects($this->once())
      ->method('build')
      ->willReturn($expected);

    // Assert.
    $actual = $mock->buildFromUuid($uuid);
    $this->assertSame($expected, $actual);
  }

  /**
   * Tests Build() with default resource.
   */
  public function testBuild() {
    // Setup.
    $mock = $this->getMockBuilder(DatastoreManagerBuilder::class)
      ->setMethods([
        'getResource',
        'getInfoProvider',
        'getLockableStorage',
        'getDatabase',
      ])
      ->disableOriginalConstructor()
      ->getMock();

    $mockResource        = $this->createMock(Resource::class);
    $mockInfoProvider    = $this->createMock(InfoProvider::class);
    $mockLockableStorage = $this->createMock(LockableBinStorage::class);
    $mockDatabase        = $this->createMock(Database::class);

    $mockHelper = $this->getMockBuilder(Helper::class)
      ->setMethods(['newDatastoreFactory'])
      ->disableOriginalConstructor()
      ->getMock();
    $this->writeProtectedProperty($mock, 'helper', $mockHelper);

    $mockFactory = $this->getMockBuilder(DatastoreManagerFactory::class)
      ->setMethods(['get'])
      ->disableOriginalConstructor()
      ->getMock();

    $expected = $this->createMock(IManager::class);

    // Expect.
    $mock->expects($this->once())
      ->method('getResource')
      ->willReturn($mockResource);

    $mock->expects($this->once())
      ->method('getInfoProvider')
      ->willReturn($mockInfoProvider);

    $mock->expects($this->once())
      ->method('getLockableStorage')
      ->willReturn($mockLockableStorage);

    $mock->expects($this->once())
      ->method('getDatabase')
      ->willReturn($mockDatabase);

    $mockHelper->expects($this->once())
      ->method('newDatastoreFactory')
      ->with(
        $mockResource,
        $mockInfoProvider,
        $mockLockableStorage,
        $mockDatabase
      )
      ->willReturn($mockFactory);

    $mockFactory->expects($this->once())
      ->method('get')
      ->willReturn($expected);

    // Assert.
    $actual = $mock->build();
    $this->assertSame($expected, $actual);
  }

  /**
   * Tests Build() with invalid resource.
   */
  public function testBuildWithInvalidResource() {
    // Setup.
    $mock = $this->getMockBuilder(DatastoreManagerBuilder::class)
      ->setMethods([
        'getResource',
        'getInfoProvider',
        'getLockableStorage',
        'getDatabase',
      ])
      ->disableOriginalConstructor()
      ->getMock();

    // Expect.
    $mock->expects($this->once())
      ->method('getResource')
      ->willReturn(NULL);

    $this->setExpectedException(\Exception::class, 'Resource is invalid or uninitialized.');

    $mock->expects($this->never())
      ->method('getInfoProvider');

    $mock->expects($this->never())
      ->method('getLockableStorage');

    $mock->expects($this->never())
      ->method('getDatabase');

    // Assert.
    $mock->build();
  }

}
