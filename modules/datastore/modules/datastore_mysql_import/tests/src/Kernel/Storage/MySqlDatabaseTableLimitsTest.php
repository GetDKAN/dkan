<?php

namespace Drupal\Tests\datastore_mysql_import\Kernel\Storage;

use Drupal\common\DataResource;
use Drupal\datastore_mysql_import\Factory\MysqlImportFactory;
use Drupal\datastore_mysql_import\Storage\MySqlDatabaseTable;
use Drupal\KernelTests\KernelTestBase;
use Procrastinator\Result;

/**
 * Find out and document what the limits are for table column names.
 *
 * @covers \Drupal\datastore_mysql_import\Storage\MySqlDatabaseTable
 * @coversDefaultClass \Drupal\datastore_mysql_import\Storage\MySqlDatabaseTable
 *
 * @group datastore
 * @group kernel
 *
 * @see \Drupal\Tests\datastore_mysql_import\Kernel\Storage\DatabaseTableLimitsTest
 */
class MySqlDatabaseTableLimitsTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'common',
    'datastore',
    'datastore_mysql_import',
    'metastore',
  ];

  public function provideColumns() {
    $columns = 400;
    $values = [];

    // Uniqid() gives items like this: 647e9f0d649d2 13 characters.
    // When in innodb_strict_mode=OFF, we get this error:
    // SQLSTATE[HY000]: General error: 1117 Too many columns: CREATE TABLE
    // "test87602183datastore_1cbe23ff091e4a60c97250fbd708f154"
    // 1117 is an error code, not a limit number.
    for ($i = 0; $i < $columns; ++$i) {
      $values[uniqid().uniqid().uniqid().uniqid().uniqid().uniqid()] = $i;
    }
    return [[$values]];
  }

  /**
   * @dataProvider provideColumns
   */
  public function testTableWidth($columns) {
    $this->markTestIncomplete('This test fails sporadically.');
    $file_path = stream_get_meta_data(tmpfile())['uri'];

    $fp = fopen($file_path, 'w');

    fputcsv($fp, array_keys($columns));
    fputcsv($fp, array_values($columns));
    fclose($fp);

    $identifier = 'id';

    $data_resource = new DataResource($file_path, 'text/csv');

    $import_factory = $this->container->get('dkan.datastore.service.factory.import');
    $this->assertInstanceOf(MysqlImportFactory::class, $import_factory);

    /** @var \Drupal\datastore\Plugin\QueueWorker\ImportJob $import_job */
    $import_job = $import_factory->getInstance($identifier, ['resource' => $data_resource])
      ->getImporter();
    $this->assertInstanceOf(MySqlDatabaseTable::class, $import_job->getStorage());


    $result = $import_job->run();
    $this->assertEquals(Result::DONE, $result->getStatus(), $result->getError());
    $this->assertEquals(1, $import_job->getStorage()
      ->count(), 'There is 1 row in the CSV.');
  }

}
