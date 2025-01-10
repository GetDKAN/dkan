<?php

namespace Drupal\Tests\datastore\Unit\Service;

use Drupal\Core\Database\Connection;
use Drupal\metastore\ResourceMapper;
use PHPUnit\Framework\TestCase;
use Drupal\datastore\DatastoreService;
use Drupal\common\DataResource;
use Drupal\datastore\PostImportResultFactory;

/**
 * Tests the PostImportResult class.
 *
 * @group dkan
 * @group datastore
 * @group unit
 */
class PostImportResultTest extends TestCase {

  /**
   * Test storeJobStatus() succeeds.
   *
   * @covers ::storeJobStatus
   */
  public function testStoreJobStatus() {
    $resource = new DataResource('test.csv', 'text/csv');

    $resourceMapperMock = $this->createMock(ResourceMapper::class);
    $resourceMapperMock->expects($this->any())
      ->method('get')
      ->withAnyParameters()
      ->willReturn($resource);

    $datastoreServiceMock = $this->createMock(DatastoreService::class);
    $datastoreServiceMock->expects($this->any())
      ->method('getResourceMapper')
      ->willReturn($resourceMapperMock);

    $queryMock = $this->getMockBuilder('stdClass')
      ->addMethods(['fields', 'execute'])
      ->getMock();

    $queryMock->expects($this->once())
      ->method('fields')
      ->with([
        'resource_identifier' => $resource->getIdentifier(),
        'resource_version' => $resource->getVersion(),
        'post_import_status' => 'done',
        'post_import_error' => '',
      ])
      ->willReturnSelf();

    $queryMock->expects($this->once())
      ->method('execute')
      ->willReturn(TRUE);

    $connectionMock = $this->createMock(Connection::class);
    $connectionMock ->expects($this->once())
      ->method('insert')
      ->with('dkan_post_import_job_status')
      ->willReturn($queryMock);

    $postImportResultFactory = new PostImportResultFactory($connectionMock, $resourceMapperMock);

    $postImportResult = $postImportResultFactory->initializeFromResource('done', '', $resource);

    $result_store = $postImportResult->storeJobStatus();

    $this->assertTrue($result_store);
  }

  /**
   * Test retrieveJobStatus() succeeds.
   *
   * @covers ::retrieveJobStatus
   */
  public function testRetrieveJobStatus() {
    $import_info = [
      '#resource_version' => 'test_version',
      '#post_import_status' => 'test_status',
      '#post_import_error' => 'test_error',
    ];

    $resource = new DataResource('test.csv', 'text/csv');

    $resourceMapperMock = $this->createMock(ResourceMapper::class);
    $resourceMapperMock->expects($this->any())
      ->method('get')
      ->withAnyParameters()
      ->willReturn($resource);

    $datastoreServiceMock = $this->createMock(DatastoreService::class);
    $datastoreServiceMock->expects($this->any())
      ->method('getResourceMapper')
      ->willReturn($resourceMapperMock);

    $resultMock = $this->getMockBuilder('stdClass')
      ->addMethods(['fetchAssoc'])
      ->getMock();

    $resultMock->expects($this->once())
      ->method('fetchAssoc')
      ->willReturn($import_info);

    $queryMock = $this->getMockBuilder('stdClass')
      ->addMethods(['condition', 'fields', 'execute'])
      ->getMock();

    $queryMock->expects($this->exactly(2))
      ->method('condition')
      ->willReturnSelf();

    $queryMock->expects($this->once())
      ->method('fields')
      ->with('dkan_post_import_job_status', [
        'resource_version',
        'post_import_status',
        'post_import_error',
      ])
      ->willReturnSelf();

    $queryMock->expects($this->once())
      ->method('execute')
      ->willReturn($resultMock);

    $connectionMock = $this->createMock(Connection::class);
    $connectionMock ->expects($this->once())
      ->method('select')
      ->with('dkan_post_import_job_status')
      ->willReturn($queryMock);

    $postImportResultFactory = new PostImportResultFactory($connectionMock, $resourceMapperMock);

    $postImportResult = $postImportResultFactory->initializeFromResource('test_status', 'test_error', $resource);

    $result_store = $postImportResult->retrieveJobStatus();

    $this->assertSame($result_store, $import_info);
  }

  /**
   * Test removeJobStatus() succeeds.
   *
   * @covers ::removeJobStatus
   */
  public function testRemoveJobStatus() {
    $resource = new DataResource('test.csv', 'text/csv');

    $resourceMapperMock = $this->createMock(ResourceMapper::class);
    $resourceMapperMock->expects($this->any())
      ->method('get')
      ->withAnyParameters()
      ->willReturn($resource);

    $datastoreServiceMock = $this->createMock(DatastoreService::class);
    $datastoreServiceMock->expects($this->any())
      ->method('getResourceMapper')
      ->willReturn($resourceMapperMock);

    $queryMock = $this->getMockBuilder('stdClass')
      ->addMethods(['condition', 'execute'])
      ->getMock();

    $queryMock->expects($this->exactly(2))
      ->method('condition')
      ->willReturnSelf();

    $queryMock->expects($this->once())
      ->method('execute')
      ->willReturn(TRUE);

    $connectionMock = $this->createMock(Connection::class);
    $connectionMock ->expects($this->once())
      ->method('delete')
      ->with('dkan_post_import_job_status')
      ->willReturn($queryMock);

    $postImportResultFactory = new PostImportResultFactory($connectionMock, $resourceMapperMock);

    $postImportResult = $postImportResultFactory->initializeFromResource('test_status', 'test_error', $resource);

    $result_store = $postImportResult->removeJobStatus();

    $this->assertTrue($result_store);
  }

}
