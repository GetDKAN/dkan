<?php

namespace Drupal\Tests\datastore_mysql_import\Kernel\Service;

use Drupal\datastore_mysql_import\Service\MySqlImportJob;
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
//    $this->markTestIncomplete('Test error state of mysql import job if table already exists.');

    $file_path = dirname(__FILE__, 4) . '/data/columnspaces.csv';
    $identifier = 'identifier';

    /** @var \Drupal\datastore_mysql_import\Factory\MySqlImportFactory $factory */
    $factory = $this->container->get('dkan.datastore_mysql_import.service.factory.import');
    $this->assertEquals(MySqlImportFactory::class, get_class($factory));

    $import_job = $factory->getInstance(
      $identifier,
      [
        'resource' => new DataResource($file_path, 'text/csv'),
      ]
    )->getImporter();

    $this->assertInstanceOf(MySqlImportJob::class, $import_job);
    $this->assertEquals(Result::STOPPED, $import_job->getResult()->getStatus());

    $storage = $import_job->getStorage();
//    $storage->setSchema(['fields' => ['a','b','c']]);
    $storage->count();

    /** @var Result $result */
    $result = $import_job->run();
//        $this->assertEquals('foo', print_r($import_job->getStorage()->getSchema(), true));
    // Result should be happy.
    $this->assertEquals(Result::DONE, $result->getStatus(), 'Error message: ' . $result->getError());

    // Do it again.
    //    $import_job = $factory->getInstance($identifier);
    /** @var Result $result */
    //    $result = $import_job->getImporter()->run();
    //    $this->assertEquals(Result::ERROR, $result->getStatus());
    //    $this->assertEquals('table already exists, dude.', $result->getError());
  }

}
