<?php

namespace Drupal\Tests\harvest\Unit\Storage;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Schema;
use Drupal\harvest\Storage\DatabaseTableFactory;
use PHPUnit\Framework\TestCase;
use MockChain\Chain;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @group dkan
 * @group harvest
 * @group unit
 */
class DatabaseTableFactoryTest extends TestCase {

  /**
   *
   */
  public function test() {
    $factory = new DatabaseTableFactory($this->getConnection(), $this->getEventDispatcher());
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

  /**
   * Getter for the event dispatcher.
   */
  public function getEventDispatcher(): EventDispatcherInterface {
    return $this->createMock(EventDispatcherInterface::class);
  }

}
