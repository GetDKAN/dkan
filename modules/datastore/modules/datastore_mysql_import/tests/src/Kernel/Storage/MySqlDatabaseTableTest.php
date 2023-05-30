<?php

namespace Drupal\Tests\datastore_mysql_import\Kernel\Storage;

use Drupal\common\DataResource;
use Drupal\Core\Database\SchemaObjectExistsException;
use Drupal\datastore_mysql_import\Factory\MysqlImportFactory;
use Drupal\datastore_mysql_import\Storage\MySqlDatabaseTable;
use Drupal\KernelTests\KernelTestBase;
use Procrastinator\Result;

/**
 * @covers \Drupal\datastore_mysql_import\Storage\MySqlDatabaseTable
 * @coversDefaultClass \Drupal\datastore_mysql_import\Storage\MySqlDatabaseTable
 *
 * @group datastore_mysql_import
 * @group kernel
 */
class MySqlDatabaseTableTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'common',
    'datastore',
    'datastore_mysql_import',
    'metastore',
  ];

  public function testTable() {
    $identifier = 'id';
    $file_path = dirname(__FILE__, 4) . '/data/columnspaces.csv';

    $data_resource = new DataResource($file_path, 'text/csv');

    $import_factory = $this->container->get('dkan.datastore.service.factory.import');
    $this->assertInstanceOf(MysqlImportFactory::class, $import_factory);

    /** @var \Drupal\datastore\Plugin\QueueWorker\ImportJob $import_job */
    $import_job = $import_factory->getInstance($identifier, ['resource' => $data_resource])
      ->getImporter();
    $result = $import_job->run();
    $this->assertEquals(Result::DONE, $result->getStatus(), $result->getError());
  }

  public function testTableDuplicateException() {
    $identifier = 'my_id';
    $file_path = dirname(__FILE__, 4) . '/data/columnspaces.csv';
    $data_resource = new DataResource($file_path, 'text/csv');

    $import_factory = $this->container->get('dkan.datastore.service.factory.import');
    $this->assertInstanceOf(MysqlImportFactory::class, $import_factory);

    /** @var \Drupal\datastore\Plugin\QueueWorker\ImportJob $import_job */
    $import_job = $import_factory->getInstance($identifier, ['resource' => $data_resource])
      ->getImporter();
    $this->assertInstanceOf(
      MySqlDatabaseTable::class,
      $db_table = $import_job->getStorage()
    );

    // Store the table.
    $result = $import_job->run();
    $this->assertEquals(Result::DONE, $result->getStatus(), $result->getError());

    // Count() will trigger setTable() again, leading to an exception.
    $this->expectException(SchemaObjectExistsException::class);
    $this->expectExceptionMessageMatches('/already exists/');
    $db_table->count();
  }

  public function testTableNoSchema() {
    $identifier = 'my_id';
    $file_path = dirname(__FILE__, 4) . '/data/columnspaces.csv';
    $data_resource = new DataResource($file_path, 'text/csv');

    $import_factory = $this->container->get('dkan.datastore.service.factory.import');
    $this->assertInstanceOf(MysqlImportFactory::class, $import_factory);

    /** @var \Drupal\datastore\Plugin\QueueWorker\ImportJob $import_job */
    $import_job = $import_factory->getInstance($identifier, ['resource' => $data_resource])
      ->getImporter();
    $this->assertInstanceOf(
      MySqlDatabaseTable::class,
      $db_table = $import_job->getStorage()
    );

    // Count() will trigger setTable(), which will throw an exception because
    // the table object does not have a schema set up yet.
    $this->expectException(\Exception::class);
    $this->expectExceptionMessage('Could not instantiate the table due to a lack of schema.');
    $db_table->count();
  }

}
