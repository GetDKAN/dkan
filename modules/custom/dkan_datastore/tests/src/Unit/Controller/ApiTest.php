<?php

namespace Drupal\Tests\dkan_datastore\Unit\Controller;

use Dkan\Datastore\Manager\IManager;
use Drupal\dkan_api\Storage\DrupalNodeDataset;
use Drupal\dkan_common\Service\Factory;
use Drupal\dkan_common\Tests\DkanTestBase;
use Drupal\dkan_datastore\Controller\Api;
use Drupal\dkan_datastore\Manager\DatastoreManagerBuilder;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @coversDefaultClass Drupal\dkan_datastore\Controller\Api
 * @group dkan_datastore
 */
class ApiTest extends DkanTestBase {

  /**
   * Tests constructor.
   */
  public function testConstructor() {
    // Setup.
    $mock = $this->getMockBuilder(Api::class)
      ->disableOriginalConstructor()
      ->getMockForAbstractClass();
    $mockContainer = $this->getMockContainer();
    $mockDkanFactory = $this->createMock(Factory::class);
    $mockStorage = $this->getMockBuilder(DrupalNodeDataset::class)
      ->disableOriginalConstructor()
      ->setMethods([
        'setSchema',
      ])
      ->getMock();
    $mockManagerBuilder = $this->createMock(DatastoreManagerBuilder::class);

    // Expect.
    $mockContainer->expects($this->exactly(3))
      ->method('get')
      ->withConsecutive(
        ['dkan.factory'],
        ['dkan_api.storage.drupal_node_dataset'],
        ['dkan_datastore.manager.datastore_manager_builder']
      )
      ->willReturnOnConsecutiveCalls(
        $mockDkanFactory,
        $mockStorage,
        $mockManagerBuilder
      );

    // Assert.
    $mock->__construct($mockContainer);
    $this->assertSame(
      $mockContainer,
      $this->readAttribute($mock, 'container')
    );
    $this->assertSame(
      $mockDkanFactory,
      $this->readAttribute($mock, 'dkanFactory')
    );
    $this->assertSame(
      $mockStorage,
      $this->readAttribute($mock, 'storage')
    );
    $this->assertSame(
      $mockManagerBuilder,
      $this->readAttribute($mock, 'managerBuilder')
    );
  }

  /**
   * Provides data for testDatasetWithSummary.
   */
  public function dataTestDatasetWithSummary() {

    $uuid = uniqid('some-uuid-');
    $resource_uuid = uniqid('resource-uuid-');

    $json_object_with_resource = (object) [
      'identifier' => $uuid,
      'distribution' => [
        (object) [
          'identifier' => $resource_uuid,
        ],
      ],
    ];
    $encoded_with_resource = json_encode($json_object_with_resource);

    $json_object_without_resource = (object) [
      'identifier' => $uuid,
    ];
    $encoded_without_resource = json_encode($json_object_without_resource);

    return [
      'dataset with resource uuid' => [
        $uuid,
        $encoded_with_resource,
      ],
      'dataset without resource uuid' => [
        $uuid,
        $encoded_without_resource,
      ],
    ];
  }

  /**
   * Tests function datasetWithSummary.
   *
   * @dataProvider dataTestDatasetWithSummary
   */
  public function testDatasetWithSummary($uuid, $dataEncoded) {
    // Setup.
    $mock = $this->getMockBuilder(Api::class)
      ->setMethods(NULL)
      ->disableOriginalConstructor()
      ->getMock();

    $mockStorage = $this->getMockBuilder(DrupalNodeDataset::class)
      ->setMethods(['retrieve'])
      ->disableOriginalConstructor()
      ->getMock();
    $this->writeProtectedProperty($mock, 'storage', $mockStorage);

    $mockManagerBuilder = $this->getMockBuilder(IManager::class)
      ->setMethods(['buildFromUuid'])
      ->disableOriginalConstructor()
      ->getMockForAbstractClass();
    $this->writeProtectedProperty($mock, 'managerBuilder', $mockManagerBuilder);

    $mockManager = $this->getMockBuilder(IManager::class)
      ->setMethods([
        'getTableHeaders',
        'numberOfRecordsImported'])
      ->disableOriginalConstructor()
      ->getMockForAbstractClass();

    $headers = [
      'foo',
      'bar',
    ];
    $rowCount = 42;

    $mockDkanFactory = $this->getMockBuilder(Factory::class)
      ->setMethods(['newJsonResponse'])
      ->disableOriginalConstructor()
      ->getMock();
    $this->writeProtectedProperty($mock, 'dkanFactory', $mockDkanFactory);
    $mockJsonResponse = $this->createMock(JsonResponse::class);

    // Expect.
    $mockStorage->expects($this->once())
      ->method('retrieve')
      ->with($uuid)
      ->willReturn($dataEncoded);

    $mockManagerBuilder->expects($this->once())
      ->method('buildFromUuid')
      ->willReturn($mockManager);

    $mockManager->expects($this->once())
      ->method('getTableHeaders')
      ->willReturn($headers);

    $mockManager->expects($this->any())
      ->method('numberOfRecordsImported')
      ->willReturn($rowCount);

    $mockDkanFactory->expects($this->once())
      ->method('newJsonResponse')
      ->willReturn($mockJsonResponse);

    // Assert.
    $actual = $mock->datasetWithSummary($uuid);
    $this->assertEquals($mockJsonResponse, $actual);
  }

  /**
   * Tests exception in function datasetWithSummary
   */
  public function testDatasetWithSummaryException() {
    // Setup.
    $uuid = uniqid('uuid-');

    $exceptionMessage = __METHOD__ . " exception message";
    $expectedException = new \Exception($exceptionMessage);

    $mock = $this->getMockBuilder(Api::class)
      ->setMethods(NULL)
      ->disableOriginalConstructor()
      ->getMock();

    $mockStorage = $this->getMockBuilder(DrupalNodeDataset::class)
      ->setMethods(['retrieve'])
      ->disableOriginalConstructor()
      ->getMock();
    $this->writeProtectedProperty($mock, 'storage', $mockStorage);

    $mockDkanFactory = $this->getMockBuilder(Factory::class)
      ->setMethods(['newJsonResponse'])
      ->disableOriginalConstructor()
      ->getMock();
    $this->writeProtectedProperty($mock, 'dkanFactory', $mockDkanFactory);
    $mockJsonResponse = $this->createMock(JsonResponse::class);

    // Expect.
    $mockStorage->expects($this->once())
      ->method('retrieve')
      ->with($uuid)
      ->willThrowException($expectedException);

    $mockDkanFactory->expects($this->once())
      ->method('newJsonResponse')
      ->willReturn($mockJsonResponse);

    // Assert.
    $actual = $mock->datasetWithSummary($uuid);
    $this->assertEquals($mockJsonResponse, $actual);
  }

}
