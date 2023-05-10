<?php

namespace Drupal\Tests\datastore_mysql_import\Kernel\Service;

use Drupal\datastore_mysql_import\Service\MySqlImportJob;
use Drupal\datastore_mysql_import\Factory\MySqlImportFactory;
use Drupal\KernelTests\KernelTestBase;
use Drupal\common\DataResource;

/**
 * @covers \Drupal\datastore_mysql_import\Factory\MySqlImportFactory
 * @coversDefaultClass \Drupal\datastore_mysql_import\Factory\MySqlImportFactory
 */
class MySqlImportFactoryTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'common',
    'datastore',
    'datastore_mysql_import',
    'metastore',
  ];

  public function testFactory() {
    $identifier = 'identifier';

    /** @var \Drupal\datastore_mysql_import\Factory\MySqlImportFactory $factory */
    $factory = $this->container->get('dkan.datastore_mysql_import.service.factory.import');
    $this->assertEquals(MySqlImportFactory::class, get_class($factory));

    /** @var \Drupal\datastore\Service\Import $import_service */
    $import_service = $factory->getInstance(
      $identifier,
      ['resource' => new DataResource('file_path.csv', 'text/csv')]
    );
    $this->assertEquals(MySqlImportJob::class, get_class($import_service->getImporter()));
  }

}
