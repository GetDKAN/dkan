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
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\dkan_datastore\Storage\Database;

/**
 * @coversDefaultClass Drupal\dkan_datastore\Manager\DatastoreManagerBuilder
 * @group dkan_datastore
 */
class DatastoreManagerBuilderTest extends DkanTestBase {

  /**
   * Tests __construct().
   */
  public function testConstruct() {
    // setup
    $mock = $this->getMockBuilder(DatastoreManagerBuilder::class)
      ->setMethods(null)
      ->disableOriginalConstructor()
      ->getMock();

    $mockContainer = $this->getMockContainer();

    // expect
    // nothing is fetch at construct
    $mockContainer->expects($this->never())
      ->method('get');

    // assert
    $mock->__construct($mockContainer);
    $this->assertSame(
      $mockContainer,
      $this->readAttribute($mock, 'container')
    );
  }

  /**
   * Tests LoadEntityByUuid().
   */
  public function testLoadEntityByUuid() {
    // setup
    $mock = $this->getMockBuilder(DatastoreManagerBuilder::class)
      ->setMethods(null)
      ->disableOriginalConstructor()
      ->getMock();

    $mockContainer = $this->getMockContainer();
    $this->writeProtectedProperty($mock, 'container', $mockContainer);

    $mockEntityRepository = $this->getMockBuilder(EntityRepositoryInterface::class)
      ->setMethods(['loadEntityByUuid'])
      ->getMockForAbstractClass();

    $mockEntity = $this->createMock(EntityInterface::class);

    $uuid = uniqid('foobar');

    // expect
    $mockContainer->expects($this->once())
      ->method('get')
      ->with('entity.repository')
      ->willReturn($mockEntityRepository);

    $mockEntityRepository->expects($this->once())
      ->method('loadEntityByUuid')
      ->with('node', $uuid)
      ->willReturn($mockEntity);

    // assert
    $actual = $this->invokeProtectedMethod($mock, 'loadEntityByUuid', $uuid);
    $this->assertSame($mockEntity, $actual);
  }

  /**
   * Tests SetResource().
   */
  public function testSetResource() {
    // setup
    $mock = $this->getMockBuilder(DatastoreManagerBuilder::class)
      ->setMethods(null)
      ->disableOriginalConstructor()
      ->getMock();

    $mockResource = $this->createMock(Resource::class);

    // assert
    $mock->setResource($mockResource);
    $this->assertSame($mockResource, $this->readAttribute($mock, 'resource'));
  }

  /**
   * Tests GetResource().
   */
  public function testGetResource() {
    // setup
    $mock = $this->getMockBuilder(DatastoreManagerBuilder::class)
      ->setMethods(null)
      ->disableOriginalConstructor()
      ->getMock();

    $mockResource = $this->createMock(Resource::class);
    $this->writeProtectedProperty($mock, 'resource', $mockResource);
    // assert
    $actual       = $this->invokeProtectedMethod($mock, 'getResource');
    $this->assertSame($mockResource, $actual);
  }

  /**
   * Tests GetDatabase().
   */
  public function testGetDatabase() {
    // setup
    $mock = $this->getMockBuilder(DatastoreManagerBuilder::class)
      ->setMethods(null)
      ->disableOriginalConstructor()
      ->getMock();

    $mockContainer = $this->getMockContainer();
    $this->writeProtectedProperty($mock, 'container', $mockContainer);

    $mockDatabase = $this->createMock(Database::class);

    // expect
    $mockContainer->expects($this->once())
      ->method('get')
      ->with('dkan_datastore.database')
      ->willReturn($mockDatabase);

    // assert
    $actual = $this->invokeProtectedMethod($mock, 'getDatabase');
    $this->assertSame($mockDatabase, $actual);
  }

  /**
   * Tests BuildFromUuid().
   */
  public function testBuildFromUuid() {
    // setup
    $mock = $this->getMockBuilder(DatastoreManagerBuilder::class)
      ->setMethods([
        'loadEntityByUuid',
        'setResourceFromFilePath',
        'build',
      ])
      ->disableOriginalConstructor()
      ->getMock();

    $mockDatasetEntity = $this->getMockBuilder(EntityInterface::class)
      ->setMethods([
        'id'
      ])
      ->disableOriginalConstructor()
      ->getMockForAbstractClass();


    $downloadUrl = 'http://foo.bar';

    $datasetValue = (object) [
        'distribution' => [
          (object) [
            'downloadURL' => $downloadUrl,
          ],
        ],
    ];

    $encodedDatasetValue = json_encode($datasetValue);

    $mockDatasetEntity->field_json_metadata = (object) [
        'value' => $encodedDatasetValue,
    ];

    $datasetEntityId = 42;
    $uuid            = uniqid('foo-uuid');

    $expected = $this->createMock(IManager::class);

    // expect
    $mock->expects($this->once())
      ->method('loadEntityByUuid')
      ->with($uuid)
      ->willReturn($mockDatasetEntity);

    $mockDatasetEntity->expects($this->once())
      ->method('id')
      ->willReturn($datasetEntityId);

    $mock->expects($this->once())
      ->method('setResourceFromFilePath')
      ->with($datasetEntityId, $downloadUrl)
      ->willReturnSelf();

    $mock->expects($this->once())
      ->method('build')
      ->willReturn($expected);

    // assert
    $actual = $mock->buildFromUuid($uuid);
    $this->assertSame($expected, $actual);
  }

  /**
   * Tests Build() with default resource.
   */
  public function testBuild() {
    // setup
    $mock = $this->getMockBuilder(DatastoreManagerBuilder::class)
      ->setMethods([
        'loadEntityByUuid',
        'getResource',
        'getInfoProvider',
        'getLockableStorage',
        'getDatabase',
        'getFactory',
      ])
      ->disableOriginalConstructor()
      ->getMock();

    $mockResource        = $this->createMock(Resource::class);
    $mockInfoProvider    = $this->createMock(InfoProvider::class);
    $mockLockableStorage = $this->createMock(LockableBinStorage::class);
    $mockDatabase        = $this->createMock(Database::class);

    $mockFactory = $this->getMockBuilder(DatastoreManagerFactory::class)
      ->setMethods(['get'])
      ->disableOriginalConstructor()
      ->getMock();

    $expected = $this->createMock(IManager::class);

    // expect

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

    $mock->expects($this->once())
      ->method('getFactory')
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

    // assert
    $actual = $mock->build();
    $this->assertSame($expected, $actual);
  }

  /**
   * Tests Build() with invalid resource.
   */
  public function testBuildWithInvalidResource() {
    // setup
    $mock = $this->getMockBuilder(DatastoreManagerBuilder::class)
      ->setMethods([
        'loadEntityByUuid',
        'getResource',
        'getInfoProvider',
        'getLockableStorage',
        'getDatabase',
        'getFactory',
      ])
      ->disableOriginalConstructor()
      ->getMock();

    // expect
    $mock->expects($this->once())
      ->method('getResource')
      ->willReturn(null);

    $this->setExpectedException(\Exception::class, 'Resource is invalid or uninitialized.');

    $mock->expects($this->never())
      ->method('getInfoProvider');

    $mock->expects($this->never())
      ->method('getLockableStorage');

    $mock->expects($this->never())
      ->method('getDatabase');

    $mock->expects($this->never())
      ->method('getFactory');

    // assert
    $mock->build();
  }

}
