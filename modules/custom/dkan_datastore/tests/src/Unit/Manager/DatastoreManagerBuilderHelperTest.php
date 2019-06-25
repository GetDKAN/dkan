<?php

namespace Drupal\Tests\dkan_datastore\Unit\Manager;

use Drupal\dkan_datastore\Manager\DatastoreManagerBuilderHelper;
use Drupal\dkan_common\Tests\DkanTestBase;
use Dkan\Datastore\Resource;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityInterface;

/**
 * @coversDefaultClass Drupal\dkan_datastore\Manager\DatastoreManagerBuilderHelper
 * @group dkan_datastore
 */
class DatastoreManagerBuilderHelperTest extends DkanTestBase {

  /**
   * Tests __construct().
   */
  public function testConstruct() {
    // Setup.
    $mock = $this->getMockBuilder(DatastoreManagerBuilderHelper::class)
      ->setMethods(NULL)
      ->disableOriginalConstructor()
      ->getMock();

    $mockContainer = $this->getMockContainer();

    // Expect
    // Nothing is fetch at construct.
    $mockContainer->expects($this->never())
      ->method('get');

    // Assert.
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
    // Setup.
    $mock = $this->getMockBuilder(DatastoreManagerBuilderHelper::class)
      ->setMethods(NULL)
      ->disableOriginalConstructor()
      ->getMock();

    $mockContainer = $this->getMockContainer();
    $this->writeProtectedProperty($mock, 'container', $mockContainer);

    $mockEntityRepository = $this->getMockBuilder(EntityRepositoryInterface::class)
      ->setMethods(['loadEntityByUuid'])
      ->getMockForAbstractClass();

    $mockEntity = $this->createMock(EntityInterface::class);

    $uuid = uniqid('foobar');

    // Expect.
    $mockContainer->expects($this->once())
      ->method('get')
      ->with('entity.repository')
      ->willReturn($mockEntityRepository);

    $mockEntityRepository->expects($this->once())
      ->method('loadEntityByUuid')
      ->with('node', $uuid)
      ->willReturn($mockEntity);

    // Assert.
    $actual = $mock->loadEntityByUuid($uuid);
    $this->assertSame($mockEntity, $actual);
  }

  /**
   * Tests LoadEntityByUuid() on exception condition.
   */
  public function testLoadEntityByUuidOnException() {
    // Setup.
    $mock = $this->getMockBuilder(DatastoreManagerBuilderHelper::class)
      ->setMethods(NULL)
      ->disableOriginalConstructor()
      ->getMock();

    $mockContainer = $this->getMockContainer();
    $this->writeProtectedProperty($mock, 'container', $mockContainer);

    $mockEntityRepository = $this->getMockBuilder(EntityRepositoryInterface::class)
      ->setMethods(['loadEntityByUuid'])
      ->getMockForAbstractClass();

    $mockEntity = NULL;

    $uuid = uniqid('foobar');

    // Expect.
    $mockContainer->expects($this->once())
      ->method('get')
      ->with('entity.repository')
      ->willReturn($mockEntityRepository);

    $mockEntityRepository->expects($this->once())
      ->method('loadEntityByUuid')
      ->with('node', $uuid)
      ->willReturn($mockEntity);

    $this->setExpectedException(\Exception::class, "Enitity {$uuid} could not be loaded.");

    // Assert.
    $mock->loadEntityByUuid($uuid);
  }

  /**
   * Tests NewResourceFromEntity().
   */
  public function testNewResourceFromEntity() {

    // Setup.
    $mock = $this->getMockBuilder(DatastoreManagerBuilderHelper::class)
      ->setMethods([
        'loadEntityByUuid',
        'newResourceFromFilePath',
        'getResourceFilePathFromEntity',
      ])
      ->disableOriginalConstructor()
      ->getMock();

    $mockDatasetEntity = $this->getMockBuilder(EntityInterface::class)
      ->setMethods(['id'])
      ->disableOriginalConstructor()
      ->getMockForAbstractClass();

    $mockResource = $this->createMock(Resource::class);

    $downloadUrl = 'http://foo.bar';

    $datasetEntityId = 42;
    $uuid            = uniqid('foo-uuid');

    // Expect.
    $mock->expects($this->once())
      ->method('loadEntityByUuid')
      ->with($uuid)
      ->willReturn($mockDatasetEntity);

    $mockDatasetEntity->expects($this->once())
      ->method('id')
      ->willReturn($datasetEntityId);

    $mock->expects($this->once())
      ->method('getResourceFilePathFromEntity')
      ->with($mockDatasetEntity)
      ->willReturn($downloadUrl);

    $mock->expects($this->once())
      ->method('newResourceFromFilePath')
      ->with($datasetEntityId, $downloadUrl)
      ->willReturn($mockResource);

    // Assert.
    $actual = $mock->newResourceFromEntity($uuid);
    $this->assertSame($mockResource, $actual);
  }

  /**
   * Tests GetResourceFilePathFromEntity().
 */
  public function testGetResourceFilePathFromEntity() {
    // Setup.
    $mock = $this->getMockBuilder(DatastoreManagerBuilderHelper::class)
      ->setMethods(null)
      ->disableOriginalConstructor()
      ->getMock();

    $mockDatasetEntity = $this->createMock(EntityInterface::class);

    $downloadUrl = 'http://foo.bar';

    $datasetValue = (object) [
      'data' =>  [
        'downloadURL' => $downloadUrl,
      ],
    ];

    $encodedDatasetValue = json_encode($datasetValue);

    $mockDatasetEntity->field_json_metadata = (object) [
      'value' => $encodedDatasetValue,
    ];

    $uuid            = uniqid('foo-uuid');
    $mockDatasetEntity->uuid = $uuid;

    // Assert.
    $actual = $mock->getResourceFilePathFromEntity($mockDatasetEntity);
    $this->assertSame($downloadUrl, $actual);
  }

  /**
   * Tests GetResourceFilePathFromEntity on exceptions().
 */
  public function testGetResourceFilePathInvalidField() {
    // Setup.
    $mock = $this->getMockBuilder(DatastoreManagerBuilderHelper::class)
      ->setMethods(null)
      ->disableOriginalConstructor()
      ->getMock();

    // entity with no fields.
    $mockDatasetEntity = $this->createMock(EntityInterface::class);

    $uuid            = uniqid('foo-uuid');
    $mockDatasetEntity->uuid = $uuid;

    // Expect.
    $this->setExpectedException(\Exception::class, "Entity for {$mockDatasetEntity->uuid} does not have required field `field_json_metadata`.");

    // Assert.
   $mock->getResourceFilePathFromEntity($mockDatasetEntity);
  }

  public function dataGetResourceFilePathInvalidMetaData() {
    return [
      [json_encode(null)],
      [json_encode([])],
      [json_encode([
          'distribution' => [
            [
              'downloadURL' => null,
            ]
          ]
        ])],
      [json_encode([
        'data' => [
          'downloadURL' => null,
        ]
      ])],
    ];
  }

  /**
   * Tests GetResourceFilePathFromEntity on exceptions().
   *
   * @dataProvider dataGetResourceFilePathInvalidMetaData
   */
  public function testGetResourceFilePathInvalidMetaData($encodedDatasetValue) {
    // Setup.
    $mock = $this->getMockBuilder(DatastoreManagerBuilderHelper::class)
      ->setMethods(null)
      ->disableOriginalConstructor()
      ->getMock();

    $mockDatasetEntity = $this->createMock(EntityInterface::class);

    $mockDatasetEntity->field_json_metadata = (object) [
        'value' => $encodedDatasetValue,
    ];

    $uuid                    = uniqid('foo-uuid');
    $mockDatasetEntity->uuid = $uuid;

    // Expect.
    $this->setExpectedException(\Exception::class, "Invalid metadata information or missing file information.");

    // Assert.
    $mock->getResourceFilePathFromEntity($mockDatasetEntity);
  }

}
