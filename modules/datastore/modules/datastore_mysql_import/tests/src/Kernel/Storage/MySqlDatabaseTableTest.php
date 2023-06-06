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

  /**
   * @see \Drupal\Tests\datastore_mysql_import\Kernel\Service\MysqlImportTest::testTableDuplicateException()
   */
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

    // This table does not have a schema yet.
    $this->assertSame([], $db_table->getSchema(), 'Does not have a schema yet.');
    $this->expectException(\Exception::class);
    $this->expectExceptionMessage('due to a lack of schema');
    // Count() will trigger setTable(), which will throw an exception about not
    // have a schema.
    $this->assertEquals(0, $db_table->count());
  }

  public function testValidate() {
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

    // Import has not occurred, so validate() should be false.
    $this->assertFalse($db_table->validate());

    // Store the table.
    $result = $import_job->run();
    $this->assertEquals(Result::DONE, $result->getStatus(), $result->getError());

    // Valid should be true since the table exists and has been imported.
    $this->assertTrue($db_table->validate());
  }

}
