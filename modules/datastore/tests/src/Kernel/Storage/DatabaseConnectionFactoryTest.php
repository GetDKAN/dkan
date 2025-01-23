<?php

declare(strict_types=1);

namespace Drupal\Tests\datastore\Kernel\Storage;

use Drupal\Core\Database\Database;
use Drupal\KernelTests\KernelTestBase;

/**
 * Find out and document what the limits are for table column names.
 *
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
    // Just getting this service should have created a special connection info
    // target.
    $this->assertNotEmpty(
      $connection_info = Database::getConnectionInfo('default')['unbuffered_datastore'] ?? []
    );
    $this->assertArrayHasKey('pdo', $connection_info);
    $this->assertFalse($connection_info['pdo'][\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY] ?? TRUE);

    $connection = $factory->getConnection();
    $this->assertEquals('default', $connection->getKey());
    $this->assertEquals('unbuffered_datastore', $connection->getTarget());
  }

}
