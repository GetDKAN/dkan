<?php

namespace Drupal\Tests\datastore\Unit\Service;

use Drupal\Core\Database\Connection;
use Drupal\metastore\ResourceMapper;
use Drupal\datastore\Service\PostImport;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\datastore\Service\ResourceProcessorCollector;
use Drupal\metastore\DataDictionary\DataDictionaryDiscoveryInterface;
use Drupal\metastore\Reference\ReferenceLookup;
use Drupal\datastore\DatastoreService;
use Drupal\common\DataResource;

/**
 * Tests the PostImport service.
 *
 * @group dkan
 * @group datastore
 * @group unit
 */
class PostImportTest extends TestCase {

  /**
   * Test storeJobStatus() succeeds.
   *
   * @covers ::storeJobStatus
   */  
  public function testStoreJobStatus() {
    $mocks = $this->getMockDependencies();

    $queryMock = $this->getMockBuilder('stdClass')
      ->addMethods(['fields', 'execute'])
      ->getMock();

    $queryMock->expects($this->once())
      ->method('fields')
      ->with([
        'resource_identifier' => 'test_identifier',
        'resource_version' => 'test_version',
        'post_import_status' => 'test_status',
        'post_import_error' => 'test_error',
      ])
      ->willReturnSelf();

    $queryMock->expects($this->once())
      ->method('execute')
      ->willReturn(TRUE);

    $mocks['connection']->expects($this->once())
      ->method('insert')
      ->with('dkan_post_import_job_status')
      ->willReturn($queryMock);

    $post_import = new PostImport(
      ...array_values($mocks),
    );

    $result_store = $post_import->storeJobStatus('test_identifier', 'test_version', 'test_status', 'test_error');

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

    $mocks = $this->getMockDependencies();

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

    $mocks['connection']->expects($this->once())
      ->method('select')
      ->with('dkan_post_import_job_status')
      ->willReturn($queryMock);

    $post_import = new PostImport(
      ...array_values($mocks),
    );

    $result_store = $post_import->retrieveJobStatus('test_identifier', 'test_version');

    $this->assertSame($result_store, $import_info);
  }

  /**
   * Test removeJobStatus() succeeds.
   *
   * @covers ::removeJobStatus
   */  
  public function testRemoveJobStatus() {
    $mocks = $this->getMockDependencies();

    $resourceMock = $this->getMockBuilder(DataResource::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['getVersion'])
      ->getMock();

    $resourceMock->expects($this->once())
      ->method('getVersion')
      ->willReturn('test_version');

    $mocks['resourceMapper']->expects($this->once())
      ->method('get')
      ->with('test_identifier')
      ->willReturn($resourceMock);

    $queryMock = $this->getMockBuilder('stdClass')
    ->addMethods(['condition', 'execute'])
    ->getMock();

    $queryMock->expects($this->exactly(2))
      ->method('condition')
      ->willReturnSelf();

    $queryMock->expects($this->once())
      ->method('execute')
      ->willReturn(TRUE);

    $mocks['connection']->expects($this->once())
      ->method('delete')
      ->with('dkan_post_import_job_status')
      ->willReturn($queryMock);

    $post_import = new PostImport(
      ...array_values($mocks),
    );

    $result_store = $post_import->removeJobStatus('test_identifier');

    $this->assertTrue($result_store);
  }

  /**
   * Setup PostImport container mocks.
   */
  public function getMockDependencies() {
    return [
      'configFactory' => $this->createMock(ConfigFactoryInterface::class),
      'logger' => $this->createMock(LoggerInterface::class),
      'resourceMapper' => $this->createMock(ResourceMapper::class),
      'resourceProcessorCollector' => $this->createMock(ResourceProcessorCollector::class),
      'dataDictionaryDiscovery' => $this->createMock(DataDictionaryDiscoveryInterface::class),
      'referenceLookup' => $this->createMock(ReferenceLookup::class),
      'datastoreService' => $this->createMock(DatastoreService::class),
      'connection' => $this->createMock(Connection::class),
    ];
   }


}
