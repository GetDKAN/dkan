<?php

namespace Drupal\Tests\datastore_mysql_import\Kernel\Service;

use Drupal\common\DataResource;
use Drupal\datastore_mysql_import\Factory\MysqlImportFactory;
use Drupal\datastore_mysql_import\Storage\MySqlDatabaseTable;
use Drupal\KernelTests\KernelTestBase;
use Procrastinator\Result;

/**
 * @covers \Drupal\datastore_mysql_import\Service\MysqlImport
 * @coversDefaultClass \Drupal\datastore_mysql_import\Service\MysqlImport
 *
 * @group datastore_mysql_import
 */
class MysqlImportTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'common',
    'datastore',
    'datastore_mysql_import',
    'metastore',
  ];

  public function testTableDuplicateException() {
    $identifier = 'my_id';
    $file_path = dirname(__FILE__, 4) . '/data/columnspaces.csv';
    $data_resource = new DataResource($file_path, 'text/csv');

    $import_factory = $this->container->get('dkan.datastore.service.factory.import');
    $this->assertInstanceOf(MysqlImportFactory::class, $import_factory);

    /** @var \Drupal\datastore\Plugin\QueueWorker\ImportJob $import_job */
    $import_job = $import_factory->getInstance(
      $identifier,
      ['resource' => $data_resource]
    )->getImporter();
    $this->assertInstanceOf(MySqlDatabaseTable::class, $import_job->getStorage());

    // Store the table.
    $result = $import_job->run();
    $this->assertEquals(Result::DONE, $result->getStatus(), $result->getError());

    // Do it again...
    $import_job = $import_factory->getInstance(
      $identifier,
      ['resource' => $data_resource]
    )->getImporter();
    // The import job aggressively keeps track of what's already done, so we
    // have to reset that.
    $import_job->getResult()->setStatus(Result::IN_PROGRESS);
    $result = $import_job->run();
    $this->assertEquals(Result::ERROR, $result->getStatus(), $result->getError());
    $this->assertStringContainsString('already exists', $result->getError());
  }

}
