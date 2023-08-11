<?php

namespace Drupal\Tests\datastore_mysql_import\Kernel\Storage;

use Drupal\common\DataResource;
use Drupal\common\Storage\ImportedItemInterface;
use Drupal\datastore\Plugin\QueueWorker\ImportJob;
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
   * Ensure that non-mysql-import tables do not implement hasBeenImported().
   *
   * We don't want DatabaseTable to be able to report that the table has already
   * been imported.
   *
   * We exercise the whole import service factory pattern here to make sure we
   * get the DatabaseTable object we expect.
   */
  public function testIsNotImportedItemInterface() {
    // Do an import.
    $identifier = 'my_id';
    $file_path = dirname(__FILE__, 4) . '/data/columnspaces.csv';
    $data_resource = new DataResource($file_path, 'text/csv');

    $import_factory = $this->container->get('dkan.datastore.service.factory.import');
    $this->assertInstanceOf(ImportServiceFactory::class, $import_factory);

    $import_job = $import_factory->getInstance($identifier, ['resource' => $data_resource])
      ->getImporter();
    $this->assertInstanceOf(ImportJob::class, $import_job);

    $table = $import_job->getStorage();
    $this->assertInstanceOf(DatabaseTable::class, $table);
    $this->assertNotInstanceOf(ImportedItemInterface::class, $table);

    // Perform the import.
    $result = $import_job->run();
    $this->assertEquals(Result::DONE, $result->getStatus(), $result->getError());
    $this->assertEquals(2, $import_job->getStorage()->count());
  }

}
