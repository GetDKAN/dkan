<?php

namespace Drupal\Tests\datastore\Unit\Storage;

use Dkan\Datastore\Resource;
use Drupal\Core\Database\Connection;
use MockChain\Chain;
use Drupal\datastore\Storage\DatabaseTable;
use Drupal\datastore\Storage\DatabaseTableFactory;
use Drupal\indexer\IndexManager;
use PHPUnit\Framework\TestCase;

/**
 *
 */
class DatabaseTableFactoryTest extends TestCase {

  /**
   * Test basic function (no indexer service).
   */
  public function test() {
    $connection = (new Chain($this))
      ->add(Connection::class, "__destruct", NULL)
      ->getMock();

    $databaseTable = (new Chain($this))
      ->add(DatabaseTable::class, "retrieveAll", [])
      ->getMock();

    $builder = $this->getMockBuilder(DatabaseTableFactory::class);
    $factory = $builder->setConstructorArgs([$connection])
      ->onlyMethods(["getDatabaseTable"])
      ->getMock();

    $factory->method("getDatabaseTable")->willReturn($databaseTable);

    $resource = new Resource("blah", "", "text/csv");
    $object = $factory->getInstance($resource->getId(), ['resource' => $resource]);
    $this->assertTrue($object instanceof DatabaseTable);
  }

  /**
   * Test we can add an indexer service.
   */
  public function testIndexer() {
    $connection = (new Chain($this))
      ->add(Connection::class, "__destruct", NULL)
      ->getMock();

    $databaseTable = (new Chain($this))
      ->add(DatabaseTable::class, "retrieveAll", [])
      ->addd("setIndexManager")
      ->getMock();
    $databaseTable->expects($this->once())
      ->method('setIndexManager');

    $builder = $this->getMockBuilder(DatabaseTableFactory::class);
    $factory = $builder->setConstructorArgs([$connection])
      ->setMethods(["getDatabaseTable"])
      ->getMock();

    $factory->method("getDatabaseTable")->willReturn($databaseTable);

    $indexerClass = $this->getMockBuilder(IndexManager::class);
    $indexer = $indexerClass->getMock();
    $factory->setIndexManager($indexer);

    $resource = new Resource("blah", "", "text/csv");
    $object = $factory->getInstance($resource->getId(), ['resource' => $resource]);
    $this->assertTrue($object instanceof DatabaseTable);
  }

}
