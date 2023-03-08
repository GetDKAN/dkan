<?php

namespace Drupal\Tests\datastore\Unit\Service;

use Drupal\Core\Database\Connection;
use Drupal\datastore\Service\PostImportResult;
use Drupal\metastore\ResourceMapper;
use PHPUnit\Framework\TestCase;

/**
 * Test case for PostImportResult service.
 *
 * @group datastore
 */
class PostImportResultTest extends TestCase {

  /**
   * The database connection mock object.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * The metastore resource mapper mock object.
   *
   * @var \Drupal\metastore\ResourceMapper
   */
  protected $resourceMapper;

  /**
   * The PostImportResult object under test.
   *
   * @var \Drupal\datastore\Service\PostImportResult
   */
  protected $postImportResult;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->connection = $this->getMockBuilder(Connection::class)
      ->disableOriginalConstructor()
      ->getMockForAbstractClass();

    $this->resourceMapper = $this->getMockBuilder(ResourceMapper::class)
      ->disableOriginalConstructor()
      ->getMockForAbstractClass();

    $this->postImportResult = new PostImportResult($this->connection, $this->resourceMapper);
  }

  /**
   * Test case for PostImportResult::storeJobStatus method.
   */
  public function testStoreJobStatus() {

    $identifier = 'abc123';
    $version = '123456';
    $postImportStatus = 'done';
    $postImportPercentDone = '100';
    $postImportError = NULL;
    

    $this->postImportResult->storeJobStatus($identifier, $version, $postImportStatus, $postImportPercentDone, $postImportError);
    
    // $count = $this->connectionMock
    //   ->query("SELECT COUNT(*) FROM dkan_post_import_job_status")
    //   ->fetchField();
    // $this->assertEquals(197, $count, 'The row has been inserted.');
  }
}