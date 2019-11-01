<?php

namespace Drupal\Tests\dkan_harvest\Unit\Storage;

use Drupal\Core\Database\Connection;
use Drupal\Core\File\FileSystem;
use Drupal\dkan_common\Tests\Mock\Chain;
use Drupal\dkan_harvest\Storage\DatabaseTable;
use Drupal\dkan_harvest\Storage\DatabaseTableFactory;
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
      ->add(Connection::class, "blah", null)
      ->getMock();

    $databaseTable = (new Chain($this))
      ->add(DatabaseTable::class, "blah", null)
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
