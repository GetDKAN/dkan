<?php

namespace Drupal\Tests\datastore\Kernel\Service;

use Drupal\Core\Database\Connection;
use Drupal\KernelTests\KernelTestBase;
use Drupal\datastore\Service\PostImport;

/**
 * Tests the PostImport service.
 *
 * @covers \Drupal\datastore\Service\PostImport
 * @coversDefaultClass \Drupal\datastore\Service\PostImport
 *
 * @group dkan
 * @group datastore
 * @group kernel
 */
class PostImportTest extends KernelTestBase {

  protected static $modules = [
    'common',
    'datastore',
    'metastore',
  ];

  /**
   * @covers ::retrieveJobStatus
   */
  public function testRetrieveJobStatusException() {
    // Mock a connection to explode.
    $connection = $this->getMockBuilder(Connection::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['select'])
      ->getMockForAbstractClass();
    $connection->expects($this->any())
      ->method('select')
      ->willThrowException(new \Exception());

    $post_import = new PostImport(
      $connection,
      $this->container->get('dkan.metastore.resource_mapper')
    );

    $this->assertFalse($post_import->retrieveJobStatus('id', '123'));
  }

  /**
   * @covers ::removeJobStatus
   */
  public function testRemoveJobStatusException() {
    // Mock a connection to explode.
    $connection = $this->getMockBuilder(Connection::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['delete'])
      ->getMockForAbstractClass();
    $connection->expects($this->any())
      ->method('delete')
      ->willThrowException(new \Exception());

    $post_import = new PostImport(
      $connection,
      $this->container->get('dkan.metastore.resource_mapper')
    );

    $this->assertFalse($post_import->retrieveJobStatus('id', '123'));
  }

}
