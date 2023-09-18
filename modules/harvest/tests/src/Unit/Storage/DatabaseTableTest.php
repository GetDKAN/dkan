<?php

namespace Drupal\Tests\harvest\Unit\Storage;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Schema;
use MockChain\Chain;
use Drupal\harvest\Storage\HarvestDatabaseTable;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Drupal\harvest\Storage\HarvestDatabaseTable
 * @group harvest
 */
class DatabaseTableTest extends TestCase {

  /**
   *
   */
  public function testConstruction() {
    $connection = (new Chain($this))
      ->add(Connection::class, "schema", Schema::class)
      ->add(Schema::class, 'tableExists', FALSE)
      ->getMock();

    $databaseTable = new HarvestDatabaseTable($connection, "blah");
    $this->assertTrue(is_object($databaseTable));
  }

}
