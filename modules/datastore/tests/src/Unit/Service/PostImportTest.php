<?php

namespace Drupal\Tests\datastore\Unit\Service;

use Drupal\Core\Database\Connection;
use Drupal\metastore\ResourceMapper;
use Drupal\datastore\service\PostImport;
use PHPUnit\Framework\TestCase;

/**
 * Tests the PostImport service.
 *
 * @group datastore
 */
class PostImportTest extends TestCase {

  /**
   * The resource mapper mock.
   *
   * @var \Drupal\metastore\ResourceMapper
   */
  protected $resourceMapper;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connectionMock;

  /**
   * The PostImport service.
   *
   * @var \Drupal\datastore\Service\PostImport
   */
  protected $postImport;

  /**
   * Tests the storeJobStatus function.
   */
  public function testStoreJobStatus() {

      $connectionMock = $this->getMockBuilder(Connection::class)
        ->disableOriginalConstructor()
        ->getMock();

      $resourceMapperMock = $this->getMockBuilder(ResourceMapper::class)
        ->disableOriginalConstructor()
        ->getMock();
  
      $queryMock = $this->getMockBuilder('stdClass')
        ->addMethods(['fields', 'execute'])
        ->getMock();

      $connectionMock->expects($this->once())
        ->method('insert')
        ->with('dkan_post_import_job_status')
        ->willReturn($queryMock);

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
  
      $postImport = new PostImport($connectionMock, $resourceMapperMock);
  
      $result_store = $postImport->storeJobStatus('test_identifier', 'test_version', 'test_status', 'test_error');
  
      // Assert that the method returned the expected result.
      $this->assertTrue($result_store);
  }

}