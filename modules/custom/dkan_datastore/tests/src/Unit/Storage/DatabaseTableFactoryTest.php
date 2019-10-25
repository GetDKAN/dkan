<?php

namespace Drupal\Tests\dkan_datastore\Unit\Storage;

use Dkan\Datastore\Resource;
use Drupal\Core\Database\Connection;
use Drupal\dkan_common\Tests\Mock\Chain;
use Drupal\dkan_datastore\Storage\DatabaseTable;
use Drupal\dkan_datastore\Storage\DatabaseTableFactory;
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

    $resource = new Resource("blah", "");
    $object = $factory->getInstance($resource->getId(), ['resource' => $resource]);
    $this->assertTrue($object instanceof DatabaseTable);
  }

}
