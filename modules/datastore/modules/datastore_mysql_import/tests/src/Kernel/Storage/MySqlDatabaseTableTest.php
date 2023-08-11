<?php

namespace Drupal\Tests\datastore_mysql_import\Kernel\Storage;

use Drupal\common\DataResource;
use Drupal\common\Storage\ImportedItemInterface;
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

  public function testWideTable() {
    $identifier = 'id';
    $file_path = dirname(__FILE__, 4) . '/data/wide_table.csv';

    $data_resource = new DataResource($file_path, 'text/csv');

    $import_factory = $this->container->get('dkan.datastore.service.factory.import');
    $this->assertInstanceOf(MysqlImportFactory::class, $import_factory);

    /** @var \Drupal\datastore\Plugin\QueueWorker\ImportJob $import_job */
    $import_job = $import_factory->getInstance($identifier, ['resource' => $data_resource])
      ->getImporter();
    $this->assertInstanceOf(MySqlDatabaseTable::class, $import_job->getStorage());

    $result = $import_job->run();
    $this->assertEquals(Result::DONE, $result->getStatus(), $result->getError());
    $this->assertEquals(4, $import_job->getStorage()->count(), 'There are 4 rows in the CSV.');
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

    // Count() will trigger setTable() again, without leading to an exception.
    // @todo Call setTable() in PR #3969.
    $this->assertEquals(2, $db_table->count());
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

  /**
   * @covers ::hasBeenImported
   */
  public function testHasBeenImported() {
    // Do an import.
    $identifier = 'my_id';
    $file_path = dirname(__FILE__, 4) . '/data/columnspaces.csv';
    $data_resource = new DataResource($file_path, 'text/csv');

    $import_factory = $this->container->get('dkan.datastore.service.factory.import');
    $this->assertInstanceOf(MysqlImportFactory::class, $import_factory);

    $import_job = $import_factory->getInstance($identifier, ['resource' => $data_resource])
      ->getImporter();

    $table = $import_job->getStorage();
    $this->assertInstanceOf(MySqlDatabaseTable::class, $table);
    $this->assertInstanceOf(ImportedItemInterface::class, $table);

    // Has not been imported yet.
    $this->assertFalse($table->hasBeenImported());

    // Perform the import.
    $result = $import_job->run();
    $this->assertEquals(Result::DONE, $result->getStatus(), $result->getError());
    $this->assertEquals(2, $import_job->getStorage()->count());

    // Has been imported.
    $this->assertTrue($table->hasBeenImported());

    // Make a whole new importer.
    $import_job = $import_job = $import_factory->getInstance($identifier, ['resource' => $data_resource])
      ->getImporter();
    // Storage should report that it's been imported.
    $this->assertTrue($import_job->getStorage()->hasBeenImported());
  }

}
