<?php

namespace Drupal\Tests\dkan_harvest\Unit\Storage;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Schema;
use Drupal\dkan_common\Tests\Mock\Chain;
use Drupal\dkan_harvest\Storage\DatabaseTable;
use PHPUnit\Framework\TestCase;

/**
 * Tests Drupal\dkan_harvest\Storage\File.
 */
class DatabaseTableTest extends TestCase {

  public function testConstruction() {
    $connection = (new Chain($this))
      ->add(Connection::class, "schema", Schema::class)
      ->add(Schema::class, 'tableExists', FALSE)
      ->getMock();

    $databaseTable = new DatabaseTable($connection, "blah");
    $this->assertTrue(is_object($databaseTable));
  }

}
