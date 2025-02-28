<?php

namespace Drupal\Tests\datastore\Unit\Storage;

use Drupal\common\DataResource;
use Drupal\datastore\Storage\DatabaseTable;
use Drupal\datastore\Storage\DatabaseTableFactory;
use Drupal\sqlite\Driver\Database\sqlite\Connection;
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
    $connection = $this->getMockBuilder(Connection::class)
      ->disableOriginalConstructor()
      ->onlyMethods([])
      ->getMock();

    $databaseTable = (new Chain($this))
      ->add(DatabaseTable::class, "retrieveAll", [])
      ->getMock();

    $builder = $this->getMockBuilder(DatabaseTableFactory::class);
    $factory = $builder->setConstructorArgs([
      $connection,
      $this->createStub(LoggerInterface::class),
    ])
      ->onlyMethods(["getDatabaseTable"])
      ->getMock();

    $factory->method("getDatabaseTable")->willReturn($databaseTable);

    $resource = new DataResource("", "text/csv");
    $object = $factory->getInstance($resource->getUniqueIdentifier(), ['resource' => $resource]);
    $this->assertTrue($object instanceof DatabaseTable);
  }

}
