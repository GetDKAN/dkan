<?php

declare(strict_types=1);

namespace Drupal\Tests\datastore\Kernel\Storage;

use Drupal\Core\Database\Database;
use Drupal\KernelTests\KernelTestBase;

/**
 * @covers \Drupal\datastore\Storage\DatabaseConnectionFactory
 * @coversDefaultClass \Drupal\datastore\Storage\DatabaseConnectionFactory
 *
 * @group dkan
 * @group datastore
 * @group kernel
 */
class DatabaseConnectionFactoryTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'common',
    'datastore',
    'metastore',
  ];

  public function testConnectionInfo() {
    /** @var \Drupal\datastore\Storage\DatabaseConnectionFactory $factory */
    $factory = $this->container->get('dkan.datastore.database_connection_factory');
    // Just getting this service should have created the special connection
    // info target.
    $this->assertNotEmpty(
      $connection_info = Database::getConnectionInfo('datastore')['default'] ?? []
    );
    $this->assertArrayHasKey('pdo', $connection_info);
    // Should be unbuffered for MySQL.
    $this->assertFalse($connection_info['pdo'][\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY] ?? TRUE);

    // Verify that the connection itself is the correct key and target.
    $connection = $factory->getConnection();
    $this->assertEquals('datastore', $connection->getKey());
    $this->assertEquals('default', $connection->getTarget());
    // Since this is a kernel test, the two targets should have the same test
    // prefix.
    $this->assertNotEmpty($connection->getPrefix());
    $this->assertEquals(
      Database::getConnection('default', 'default')->getPrefix(),
      $connection->getPrefix()
    );
  }

}
