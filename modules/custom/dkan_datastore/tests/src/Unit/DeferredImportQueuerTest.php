<?php

namespace Drupal\Tests\dkan_datastore\Unit;

use Drupal\dkan_datastore\DeferredImportQueuer;
use Drupal\dkan_common\Tests\DkanTestBase;
use Dkan\Datastore\Resource;
use Drupal\Core\Queue\QueueInterface;

/**
 * @coversDefaultClass Drupal\dkan_datastore\DeferredImportQueuer
 * @group              dkan_datastore
 */
class DeferredImportQueuerTest extends DkanTestBase {

  /**
   * Tests CreateDeferredResourceImport().
   */
  public function testCreateDeferredResourceImport() {
    // Setup.
    $mock = $this->getMockBuilder(DeferredImportQueuer::class)
      ->setMethods([
        'getQueue',
      ])
      ->disableOriginalConstructor()
      ->getMock();

    $mockQueue = $this->getMockBuilder(QueueInterface::class)
      ->setMethods(['createItem'])
      ->disableOriginalConstructor()
      ->getMockForAbstractClass();

    $mockResource = $this->getMockBuilder(Resource::class)
      ->setMethods(
              [
                'getId',
                'getFilePath',
              ]
          )
      ->disableOriginalConstructor()
      ->getMock();

    $uuid             = uniqid('uuid');
    $resourceId       = uniqid('42');
    $resourceFilePath = uniqid('scheme://foo/bar');
    $importConfig     = ['foo'];
    $expected         = 42;

    // Expect.
    $mock->expects($this->once())
      ->method('getQueue')
      ->willReturn($mockQueue);

    $mockResource->expects($this->once())
      ->method('getId')
      ->willReturn($resourceId);

    $mockResource->expects($this->once())
      ->method('getFilePath')
      ->willReturn($resourceFilePath);

    $mockQueue->expects($this->once())
      ->method('createItem')
      ->with(
              [
                'uuid'          => $uuid,
                'resource_id'   => $resourceId,
                'file_path'     => $resourceFilePath,
                'import_config' => $importConfig,
              ]
          )
      ->willReturn($expected);

    // Assert.
    $actual = $mock->createDeferredResourceImport(
          $uuid,
          $mockResource,
          $importConfig
      );
    $this->assertEquals($expected, $actual);
  }

  /**
   * Tests CreateDeferredResourceImport() for exception condition.
   */
  public function testCreateDeferredResourceImportOnException() {
    // Setup.
    $mock = $this->getMockBuilder(DeferredImportQueuer::class)
      ->setMethods([
        'getQueue',
      ])
      ->disableOriginalConstructor()
      ->getMock();

    $mockQueue = $this->getMockBuilder(QueueInterface::class)
      ->setMethods(['createItem'])
      ->disableOriginalConstructor()
      ->getMockForAbstractClass();

    $mockResource = $this->getMockBuilder(Resource::class)
      ->setMethods(
              [
                'getId',
                'getFilePath',
              ]
          )
      ->disableOriginalConstructor()
      ->getMock();

    $uuid             = uniqid('uuid');
    $resourceId       = uniqid('42');
    $resourceFilePath = uniqid('scheme://foo/bar');
    $importConfig     = ['foo'];
    $queueId          = FALSE;

    // Expect.
    $mock->expects($this->once())
      ->method('getQueue')
      ->willReturn($mockQueue);

    $mockResource->expects($this->once())
      ->method('getId')
      ->willReturn($resourceId);

    $mockResource->expects($this->once())
      ->method('getFilePath')
      ->willReturn($resourceFilePath);

    $mockQueue->expects($this->once())
      ->method('createItem')
      ->with(
              [
                'uuid'          => $uuid,
                'resource_id'   => $resourceId,
                'file_path'     => $resourceFilePath,
                'import_config' => $importConfig,
              ]
          )
      ->willReturn($queueId);

    $this->setExpectedException(\RuntimeException::class, "Failed to create file fetcher queue for {$uuid}");

    // Assert.
    $actual = $mock->createDeferredResourceImport(
          $uuid,
          $mockResource,
          $importConfig
      );
    $this->assertEquals($queueId, $actual);
  }

}
