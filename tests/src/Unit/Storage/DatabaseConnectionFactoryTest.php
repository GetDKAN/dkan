<?php

namespace Drupal\Tests\common\Unit\Storage;

use Drupal\Core\Database\Connection;
use Drupal\Tests\common\Unit\Mocks\DatabaseConnectionFactoryMock;

use MockChain\Chain;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Drupal\common\Storage\DatabaseConnectionFactory
 * @coversDefaultClass \Drupal\common\Storage\DatabaseConnectionFactory
 *
 * @group dkan
 * @group common
 * @group unit
 */
class DatabaseConnectionFactoryTest extends TestCase {

  /**
   * @covers ::prepareConnection
   */
  public function testConnectionTimeout(): void {
    $connection_chain = (new Chain($this))
      ->add(Connection::class, 'query', StatementInterface::class, 'query');

    $timeout = 16;
    $database_connection_factory = new DatabaseConnectionFactoryMock($connection_chain->getMock());
    $database_connection_factory->setConnectionTimeout($timeout);
    $database_connection_factory->getConnection();
    $this->assertEquals("SET SESSION wait_timeout = {$timeout}", $connection_chain->getStoredInput('query')[0]);
  }

}
