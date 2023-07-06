<?php

/**
 * Manage namespaces so they don't get too long for deprecated table names.
 */

namespace Drupal\Tests\common\Kernel\Storage {

  use Drupal\common\Storage\JobStore;
  use Drupal\KernelTests\KernelTestBase;

  /**
   * @covers \Drupal\common\Storage\JobStore
   * @coversDefaultClass \Drupal\common\Storage\JobStore
   *
   * @group dkan
   * @group common
   * @group kernel
   */
  class JobStoreTest extends KernelTestBase {

    protected static $modules = [
      'common',
    ];

    public function testDeprecatedTable() {
      $db = $this->container->get('database');
      // Make a JobStore object.
      /** @var \Drupal\common\Storage\JobStoreFactory $job_store_factory */
      $job_store_factory = $this->container->get('dkan.common.job_store');
      $job_store = $job_store_factory->getInstance(\DkanTestJobSubclass::class);

      $this->assertInstanceOf(JobStore::class, $job_store);

      // First, get the table name without deprecation.
      $ref_get_table_name = new \ReflectionMethod($job_store, 'getTableName');
      $ref_get_table_name->setAccessible(TRUE);
      $table_name = $ref_get_table_name->invoke($job_store);

      // This table does not exist.
      $this->assertFalse(
        $db->schema()->tableExists($table_name)
      );

      // Use the deprecated table name.
      $ref_get_deprecated_table_name = new \ReflectionMethod($job_store, 'getDeprecatedTableName');
      $ref_get_deprecated_table_name->setAccessible(TRUE);
      $deprecated_table_name = $ref_get_deprecated_table_name->invoke($job_store);
      $ref_table_name = new \ReflectionProperty($job_store, 'tableName');
      $ref_table_name->setAccessible(TRUE);
      $ref_table_name->setValue($job_store, $deprecated_table_name);

      // Write the table. Count() will create the table before counting rows.
      $this->assertEquals(0, $job_store->count());
      // Assert that the deprecated table exists.
      $this->assertTrue(
        $db->schema()->tableExists($deprecated_table_name)
      );
      // Assert that the non-deprecated table does not exist.
      $this->assertFalse(
        $db->schema()->tableExists($table_name)
      );

      // Unset the table name, which should cause JobStore to decide to use the
      // deprecated table name on its own, since we already have a table for it.
      $ref_table_name->setValue($job_store, '');
      $this->assertEquals(
        $deprecated_table_name,
        $ref_get_table_name->invoke($job_store)
      );

      // Finally, remove the table, unset the job's table name, and it should
      // create a new table with the non-deprecated name.
      $job_store->destruct();
      $this->assertFalse(
        $db->schema()->tableExists($deprecated_table_name)
      );
      $this->assertFalse(
        $db->schema()->tableExists($table_name)
      );
      $ref_table_name->setValue($job_store, '');
      $this->assertEquals(0, $job_store->count());
      $this->assertTrue(
        $db->schema()->tableExists($table_name)
      );
    }

  }
}

/**
 * We must manage namespaces so that we don't end up with a too-long table name.
 */

namespace {

  use Procrastinator\Job\Job;

  class DkanTestJobSubclass extends Job {

    protected function runIt() {
    }

  }
}
