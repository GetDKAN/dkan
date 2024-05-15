<?php

namespace Drupal\Tests\datastore_mysql_import\Kernel\Storage;

use Drupal\datastore\DatastoreResource;
use Drupal\datastore_mysql_import\Storage\MySqlDatabaseTable;
use Drupal\KernelTests\KernelTestBase;

/**
 * @covers \Drupal\datastore_mysql_import\Storage\MySqlDatabaseTableFactory
 * @coversDefaultClass \Drupal\datastore_mysql_import\Storage\MySqlDatabaseTableFactory
 *
 * @group datastore_mysql_import
 */
class MySqlDatabaseTableFactoryTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'common',
    'datastore',
    'datastore_mysql_import',
    'metastore',
  ];

  public function testFactoryServiceResourceException() {
    /** @var \Drupal\datastore_mysql_import\Storage\MySqlDatabaseTableFactory $factory */
    $factory = $this->container->get('dkan.datastore_mysql_import.database_table_factory');
    $this->expectException(\Exception::class);
    $this->expectExceptionMessage("config['resource'] is required");
    $table = $factory->getInstance('id', []);
  }

  public function testFactoryService() {
    $identifier = 'identifier';
    $file_path = dirname(__FILE__, 4) . '/data/columnspaces.csv';
    $datastore_resource = new DatastoreResource(
      $identifier,
      $file_path,
      'text/csv'
    );

    /** @var \Drupal\datastore_mysql_import\Storage\MySqlDatabaseTableFactory $factory */
    $factory = $this->container->get('dkan.datastore_mysql_import.database_table_factory');
    $table = $factory->getInstance('id', ['resource' => $datastore_resource]);
    $this->assertInstanceOf(MySqlDatabaseTable::class, $table);
  }

}
