<?php

namespace Drupal\Tests\harvest\Unit\Storage;

use Drupal\Core\Database\Connection;
use MockChain\Chain;
use Drupal\harvest\Storage\DatabaseTable;
use Drupal\harvest\Storage\DatabaseTableFactory;
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
      ->add(Connection::class, "blah", NULL)
      ->getMock();

    $databaseTable = (new Chain($this))
      ->add(DatabaseTable::class, "blah", NULL)
      ->getMock();

    $factory = $this->getMockBuilder(DatabaseTableFactory::class)
      ->setConstructorArgs([$connection])
      ->setMethods(['getDatabaseTable'])
      ->getMock();

    $factory->method('getDatabaseTable')->willReturn($databaseTable);

    $fileStorage = $factory->getInstance('blah');
    $fileStorage2 = $factory->getInstance('blah');
    $this->assertEquals($fileStorage, $fileStorage2);
  }

}
