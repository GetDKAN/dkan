<?php

declare(strict_types=1);

namespace Drupal\Tests\dkan_datastore\Unit\Service\Factory;

use Drupal\dkan_datastore\Service\Factory\ImportServiceFactory;
use Drupal\dkan_datastore\Storage\DatabaseTableFactory;
use Drupal\dkan_datastore\Storage\ImportJobStoreFactory;
use Drupal\dkan_metastore\Reference\ReferenceLookup;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @covers \Drupal\dkan_datastore\Service\Factory\ImportServiceFactory
 * @coversDefaultClass \Drupal\dkan_datastore\Service\Factory\ImportServiceFactory
 *
 * @group dkan
 * @group dkan_datastore
 * @group unit
 */
class ImportServiceFactoryTest extends TestCase {

  /**
   * @covers ::getInstance
   */
  public function testGetInstanceException() {
    $factory = new ImportServiceFactory(
      $this->getMockBuilder(ImportJobStoreFactory::class)
        ->disableOriginalConstructor()
        ->getMock(),
      $this->getMockBuilder(DatabaseTableFactory::class)
        ->disableOriginalConstructor()
        ->getMock(),
      $this->createStub(LoggerInterface::class),
      $this->createStub(EventDispatcherInterface::class),
      $this->createStub(ReferenceLookup::class)
    );

    $this->expectException(\Exception::class);
    $this->expectExceptionMessage("config['resource'] is required");
    $factory->getInstance('id', []);
  }

}
