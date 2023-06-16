<?php

namespace Drupal\Tests\datastore_mysql_import\Kernel\Storage;

use Drupal\common\DataResource;
use Drupal\datastore\Service\Factory\ImportServiceFactory;
use Drupal\datastore\Storage\DatabaseTable;
use Drupal\KernelTests\KernelTestBase;
use Procrastinator\Result;

/**
 * @covers \Drupal\datastore\Storage\DatabaseTable
 * @coversDefaultClass \Drupal\datastore\Storage\DatabaseTable
 *
 * @group datastore
 * @group kernel
 */
class DatabaseTableTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'common',
    'datastore',
    'metastore',
  ];

  /**
   * @covers ::hasBeenImported
   */
  public function testHasBeenImported() {
    // Do an import.
    $identifier = 'my_id';
    $file_path = dirname(__FILE__, 4) . '/data/columnspaces.csv';
    $data_resource = new DataResource($file_path, 'text/csv');

    $import_factory = $this->container->get('dkan.datastore.service.factory.import');
    $this->assertInstanceOf(ImportServiceFactory::class, $import_factory);

    $import_job = $import_factory->getInstance($identifier, ['resource' => $data_resource])
      ->getImporter();

    $table = $import_job->getStorage();
    $this->assertInstanceOf(DatabaseTable::class, $table);

    // Has not been imported yet.
    $this->assertFalse($table->hasBeenImported());

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
