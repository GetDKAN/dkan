<?php

namespace Drupal\Tests\datastore\Kernel\Storage;

use Drupal\datastore\DatastoreResource;
use Drupal\Core\Database\Connection;
use Drupal\KernelTests\KernelTestBase;
use MockChain\Chain;
use Drupal\datastore\Storage\DatabaseTable;
use Drupal\datastore\Storage\DatabaseTableFactory;
use Drupal\indexer\IndexManager;
use PHPUnit\Framework\TestCase;

/**
 * @group datastore
 * @group kernel
 */
class DatabaseTableFactoryTest extends KernelTestBase {

  protected static $modules = [
    'common',
    'datastore',
    'metastore',
  ];

  /**
   * Test basic function (no indexer service).
   */
  public function test() {
    $this->markTestIncomplete('do kernel stuff');
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

    $resource = new DatastoreResource("blah", "", "text/csv");
    $object = $factory->getInstance($resource->getId(), ['resource' => $resource]);
    $this->assertTrue($object instanceof DatabaseTable);
  }

}
