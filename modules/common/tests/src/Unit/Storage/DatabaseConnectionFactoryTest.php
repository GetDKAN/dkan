<?php

namespace Drupal\Tests\common\Unit\Storage;

use Drupal\common\Storage\DatabaseConnectionFactory;

use PHPUnit\Framework\TestCase;

/**
 * Tests DatabaseConnectionFactory class.
 */
class DatabaseConnectionFactoryTest extends TestCase {

  /**
   * Test DatabaseConnectionFactory::getConnection() method.
   */
  public function testGetConnection(): void {
    $connection_factory = new DatabaseConnectionFactory();
    $connection = $connection_factory->getConnection();

    // Ensure a database connection is created with the proper target and key.
    $this->assertEquals('default', $connection->getTarget());
    $this->assertEquals('default', $connection->getKey());
  }

  /**
   * Test DatabaseConnectionFactory::setConnectionTimeout() method.
   */
  public function testSetConnectionTimeout(): void {
    $set_timeout = 10000;
    $connection_factory = new DatabaseConnectionFactory();
    $connection_factory->setConnectionTimeout($set_timeout);

    $connection = $connection_factory->getConnection();
    ['Value' => $fetched_timeout] = $connection->query("SHOW VARIABLES LIKE 'wait_timeout';")->fetchAssoc();

    // Ensure the proper wait_timeout is set for the created connection.
    $this->assertEquals($set_timeout, $fetched_timeout);
  }

}
