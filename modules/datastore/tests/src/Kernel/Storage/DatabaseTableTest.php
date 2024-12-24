<?php

namespace Drupal\Tests\datastore\Kernel\Storage;

use ColinODell\PsrTestLogger\TestLogger;
use Drupal\common\DataResource;
use Drupal\common\Storage\ImportedItemInterface;
use Drupal\datastore\DatastoreResource;
use Drupal\datastore\Plugin\QueueWorker\ImportJob;
use Drupal\datastore\Service\Factory\ImportServiceFactory;
use Drupal\datastore\Storage\DatabaseTable;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\common\Unit\Connection;
use Procrastinator\Result;

/**
 * @covers \Drupal\datastore\Storage\DatabaseTable
 * @coversDefaultClass \Drupal\datastore\Storage\DatabaseTable
 *
 * @group dkan
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

  public static function providePrepareData() {
    return [
      // Bad JSON results in a NULL on decode.
      [
        'Error decoding id:@id, data: @data.',
        'Import for  error when decoding "badjson""',
        '"badjson""',
      ],
      // The decoded JSON is supposed to be an array.
      [
        'Array expected while decoding id:@id, data: @data.',
        'Import for  returned an error when preparing table header: {"this_is": "an_object"}',
        '{"this_is": "an_object"}',
      ],
    ];
  }

  /**
   * @covers ::prepareData
   * @dataProvider providePrepareData
   */
  public function testPrepareDataLogging($expected_log, $expected_exception, $data) {
    // Get a logger that we can assert against.
    $logger = new TestLogger();

    // We have to mock tableExist() because otherwise it tries to talk to the
    // database, which we don't actually have right now.
    $database_table = $this->getMockBuilder(DatabaseTable::class)
      ->onlyMethods(['tableExist'])
      ->setConstructorArgs([
        $this->createStub(Connection::class),
        $this->createStub(DatastoreResource::class),
        $logger,
      ])
      ->getMock();
    $database_table->expects($this->any())
      ->method('tableExist')
      ->willReturn(FALSE);

    $ref_prepare_data = new \ReflectionMethod($database_table, 'prepareData');
    $ref_prepare_data->setAccessible(TRUE);

    // We can't use expectException() because we also want to look at the log.
    try {
      $ref_prepare_data->invokeArgs($database_table, [$data]);
      $this->assertTrue(FALSE, 'We expected this call to throw an exception.');
    }
    catch (\Exception $e) {
      $this->assertSame($e->getMessage(), $expected_exception);
      $this->assertTrue(
        $logger->hasErrorThatContains($expected_log)
      );
    }
  }

}
