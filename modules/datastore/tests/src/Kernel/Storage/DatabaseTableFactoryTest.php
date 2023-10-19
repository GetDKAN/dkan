<?php

namespace Drupal\Tests\datastore\Kernel\Storage;

use Drupal\datastore\DatastoreResource;
use Drupal\datastore\Storage\DatabaseTable;
use Drupal\KernelTests\KernelTestBase;

/**
 * @covers \Drupal\datastore\Storage\DatabaseTableFactory
 *
 * @group datastore
 * @group kernel
 */
class DatabaseTableFactoryTest extends KernelTestBase {

  protected static $modules = [
    'common',
    'datastore',
    'metastore',
  ];

  /**
   * Test basic function (no indexer service).
   */
  public function test() {
    /** @var \Drupal\datastore\Storage\DatabaseTableFactory $database_table_factory */
    $database_table_factory = $this->container->get('dkan.datastore.database_table_factory');

    $resource = new DatastoreResource('blah', '', 'text/csv');
    $this->assertInstanceOf(
      DatabaseTable::class,
      $database_table_factory->getInstance($resource->getId(), ['resource' => $resource])
    );
  }

}
