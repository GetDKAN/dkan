<?php

namespace Drupal\Tests\datastore\Unit\Storage;

use Drupal\Core\Database\Connection;
use Drupal\datastore\DatastoreResource;
use Drupal\datastore\Storage\DatabaseTable;
use Drupal\datastore\Storage\DatabaseTableFactory;
use MockChain\Chain;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @group dkan
 * @group datastore
 * @group unit
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
    $factory = $builder->setConstructorArgs([
        $connection,
        $this->createStub(LoggerInterface::class
      )])
      ->onlyMethods(["getDatabaseTable"])
      ->getMock();

    $factory->method("getDatabaseTable")->willReturn($databaseTable);

    $resource = new DatastoreResource("blah", "", "text/csv");
    $object = $factory->getInstance($resource->getId(), ['resource' => $resource]);
    $this->assertTrue($object instanceof DatabaseTable);
  }

}
