<?php

declare(strict_types=1);

namespace Drupal\Tests\harvest\Unit\Storage;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Schema;
use MockChain\Chain;
use Drupal\harvest\Storage\DatabaseTable;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @coversDefaultClass \Drupal\harvest\Storage\DatabaseTable
 *
 * @group dkan
 * @group harvest
 * @group unit
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

    $eventDispatcher = $this->createMock(EventDispatcherInterface::class);

    $databaseTable = new DatabaseTable($connection, "blah", $eventDispatcher);
    $this->assertTrue(is_object($databaseTable));
  }

}
