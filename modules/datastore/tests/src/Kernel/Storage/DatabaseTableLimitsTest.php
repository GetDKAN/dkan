<?php

namespace Drupal\Tests\datastore_mysql_import\Kernel\Storage;

use Drupal\common\DataResource;
use Drupal\datastore\Service\Factory\ImportServiceFactory;
use Drupal\datastore\Storage\DatabaseTable;
use Drupal\KernelTests\KernelTestBase;
use org\bovigo\vfs\vfsStream;
use Procrastinator\Result;

/**
 * Find out and document what the limits are for table column names.
 *
 * @covers \Drupal\datastore\Storage\DatabaseTable
 * @coversDefaultClass \Drupal\datastore\Storage\DatabaseTable
 *
 * @group datastore
 * @group kernel
 *
 * @see \Drupal\Tests\datastore_mysql_import\Kernel\Storage\MySqlDatabaseTableLimitsTest
 */
class DatabaseTableLimitsTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'common',
    'datastore',
    'metastore',
  ];

  public function provideColumns() {
    $columns = 197;
    $values = [];

    // Uniqid() gives items like this: 647e9f0d649d2 13 characters.
    // When in innodb_strict_mode=ON, more than 197 of them gives this error,
    // regardless of column name width:
    // SQLSTATE[42000]: Syntax error or access violation: 1118 Row size too
    // large (> 8126).
    // 1118 is an error code, not a limit number.
    for ($i = 0; $i < $columns; ++$i) {
      $values[uniqid().uniqid().uniqid().uniqid().uniqid().uniqid()] = $i;
    }
    return [[$values]];
  }

  /**
   * @dataProvider provideColumns
   */
  public function testTableWidth($columns) {
    $root = vfsStream::setup();
    $file_path = $root->url() . '/text.csv';

    $fp = fopen($file_path, 'w');

    fputcsv($fp, array_keys($columns));
    fputcsv($fp, array_values($columns));
    fclose($fp);

    $identifier = 'id';

    $data_resource = new DataResource($file_path, 'text/csv');

    $import_factory = $this->container->get('dkan.datastore.service.factory.import');
    $this->assertInstanceOf(ImportServiceFactory::class, $import_factory);

    /** @var \Drupal\datastore\Plugin\QueueWorker\ImportJob $import_job */
    $import_job = $import_factory->getInstance($identifier, ['resource' => $data_resource])
      ->getImporter();
    $this->assertInstanceOf(DatabaseTable::class, $import_job->getStorage());


    $result = $import_job->run();
    $this->assertEquals(Result::DONE, $result->getStatus(), $result->getError());
    $this->assertEquals(1, $import_job->getStorage()
      ->count(), 'There is 1 row in the CSV.');
  }

}
