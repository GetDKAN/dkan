<?php

namespace Drupal\Tests\harvest\Storage;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Schema;
use Drupal\harvest\Storage\DatabaseTableFactory;
use Drupal\Tests\UnitTestCase;
use MockChain\Chain;

/**
 *
 */
class DatabaseTableFactoryTest extends UnitTestCase {

  /**
   *
   */
  public function test() {
    $factory = new DatabaseTableFactory($this->getConnection());
    $this->assertNotNull($factory->getInstance('blah', []));
  }

  /**
   * Getter.
   */
  public function getConnection(): Connection {
    return (new Chain($this))
      ->add(Connection::class, 'schema', Schema::class)
      ->add(Schema::class, 'tableExists', FALSE)
      ->getMock();
  }

}
