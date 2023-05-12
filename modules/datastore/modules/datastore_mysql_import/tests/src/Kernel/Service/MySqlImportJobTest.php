<?php

namespace Drupal\Tests\datastore_mysql_import\Kernel\Service;

use Drupal\common\Storage\AbstractDatabaseTable;
use Drupal\datastore_mysql_import\Service\MySqlImportJob;
use Drupal\datastore_mysql_import\Storage\MySqlDatabaseTable;
use Drupal\KernelTests\KernelTestBase;
use Procrastinator\Result;
use Drupal\common\DataResource;
use Drupal\datastore_mysql_import\Factory\MySqlImportFactory;

/**
 */
class MySqlImportJobKernelTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'common',
    'datastore',
    'datastore_mysql_import',
    'metastore',
  ];

  public function testBadFilePath() {
    $identifier = 'identifier';

    /** @var \Drupal\datastore_mysql_import\Factory\MySqlImportFactory $factory */
    $factory = $this->container->get('dkan.datastore_mysql_import.service.factory.import');
    $this->assertEquals(MySqlImportFactory::class, get_class($factory));

    $import_job = $factory->getInstance(
      $identifier,
      [
        'resource' => new DataResource('file_path_does_not_exist.csv', 'text/csv'),
      ]
    )->getImporter();

    // Did we get the right kind of importer?
    $this->assertEquals(MySqlImportJob::class, get_class($import_job));

    /** @var Result $result */
    $result = $import_job->run();
    $this->assertEquals(Result::ERROR, $result->getStatus(), 'Error message: ' . $result->getError());
    $this->assertStringContainsString(
      'Unable to resolve file name "file_path_does_not_exist.csv" for resource',
      $result->getError()
    );
  }

  public function testExistingTable() {
//    $this->markTestIncomplete('Kernel test error state of mysql import job if table already exists.');

    $file_path = dirname(__FILE__, 4) . '/data/columnspaces.csv';
    $identifier = 'identifier';
    $data_resource = new DataResource($file_path, 'text/csv');

    /** @var \Drupal\datastore_mysql_import\Factory\MySqlImportFactory $import_factory */
    $import_factory = $this->container->get('dkan.datastore_mysql_import.service.factory.import');
    $this->assertInstanceOf(MySqlImportFactory::class, $import_factory);

    $import_job = $import_factory->getInstance(
      $identifier,
      [
        'resource' => $data_resource,
      ]
    )->getImporter();

    $this->assertInstanceOf(MySqlImportJob::class, $import_job);
    $this->assertEquals(Result::STOPPED, $import_job->getResult()->getStatus());

    $storage = $import_job->getStorage();
    $this->assertInstanceOf(MySqlDatabaseTable::class, $storage);

    /** @var Result $result */
    $result = $import_job->run();

    $this->assertTrue(
      $storage->getConnection()
        ->schema()
        ->tableExists($storage->getTableName())
    );

    // Result should be happy.
    $this->assertEquals(Result::DONE, $result->getStatus(), 'Error message: ' . $result->getError());

    // Do it again..................
    $this->assertInstanceOf(MySqlImportFactory::class, $import_factory);

    $second_import_job = $import_factory->getInstance(
      $identifier,
      [
        'resource' => $data_resource,
      ]
    )->getImporter();

    $this->assertInstanceOf(MySqlImportJob::class, $second_import_job);
    $this->assertEquals(Result::STOPPED, $second_import_job->getResult()
      ->getStatus());

    /** @var Result $result */
    $result = $second_import_job->run();
    $this->assertEquals(Result::ERROR, $result->getStatus());
    $this->assertEquals('table already exists, dude.', $result->getError());
  }

}
