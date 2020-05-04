<?php

namespace Drupal\Tests\datastore\Unit\Storage;

use Dkan\Datastore\Resource;
use Drupal\Core\Database\Connection;
use MockChain\Chain;
use Drupal\datastore\Storage\DatabaseTable;
use Drupal\datastore\Storage\DatabaseTableFactory;
use PHPUnit\Framework\TestCase;

/**
 *
 */
class DatabaseTableFactoryTest extends TestCase {

  /**
   *
   */
  public function test() {
    $connection = (new Chain($this))
      ->add(Connection::class, "destroy", NULL)
      ->getMock();

    $databaseTable = (new Chain($this))
      ->add(DatabaseTable::class, "retrieveAll", [])
      ->getMock();

    $builder = $this->getMockBuilder(DatabaseTableFactory::class);
    $factory = $builder->setConstructorArgs([$connection])
      ->setMethods(["getDatabaseTable"])
      ->getMock();

    $factory->method("getDatabaseTable")->willReturn($databaseTable);

    $resource = new Resource("blah", "", "text/csv");
    $object = $factory->getInstance($resource->getId(), ['resource' => $resource]);
    $this->assertTrue($object instanceof DatabaseTable);
  }

}
