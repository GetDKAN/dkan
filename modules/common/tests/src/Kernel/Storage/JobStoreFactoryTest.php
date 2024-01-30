<?php

/**
 * Manage namespaces so they don't get too long for deprecated table names.
 */

namespace Drupal\Tests\common\Kernel\Storage {

  use Drupal\common\Storage\JobStore;
  use Drupal\KernelTests\KernelTestBase;

  /**
   * @covers \Drupal\common\Storage\JobStoreFactory
   * @coversDefaultClass \Drupal\common\Storage\JobStoreFactory
   *
   * @group dkan
   * @group common
   * @group kernel
   * @group legacy
   *
   * @see \Drupal\Tests\common\Kernel\Storage\AbstractJobStoreFactoryTest
   */
  class JobStoreFactoryTest extends KernelTestBase {

    protected static $modules = [
      'common',
    ];

    public function testDeprecatedClassnameTable() {
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
      $this->assertEquals('jobstore_3358540512_dkantestjobsubclass', $table_name);

      // This table does not exist.
      $this->assertFalse(
        $db->schema()->tableExists($table_name)
      );

      // Use the deprecated table name.
      $ref_get_deprecated_table_name = new \ReflectionMethod($job_store_factory, 'getDeprecatedTableName');
      $ref_get_deprecated_table_name->setAccessible(TRUE);
      $deprecated_table_name = $ref_get_deprecated_table_name->invokeArgs(
        $job_store_factory,
        [\DkanTestJobSubclass::class]
      );
      $this->assertEquals('jobstore_dkantestjobsubclass', $deprecated_table_name);
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

      // Create a new JobStore object. The factory should see that the
      // deprecated table already exists and try to use it as the table name.
      $job_store = $job_store_factory->getInstance(\DkanTestJobSubclass::class);
      $this->assertEquals(
        $deprecated_table_name,
        $ref_get_table_name->invoke($job_store)
      );

      // Remove the table and create yet another job store object. This
      // one should have the non-deprecated table name.
      $job_store->destruct();
      $this->assertFalse(
        $db->schema()->tableExists($deprecated_table_name)
      );
      $this->assertFalse(
        $db->schema()->tableExists($table_name)
      );
      $job_store = $job_store_factory->getInstance(\DkanTestJobSubclass::class);
      $this->assertEquals($table_name, $ref_get_table_name->invoke($job_store));
      $this->assertEquals(0, $job_store->count());
      $this->assertTrue(
        $db->schema()->tableExists($table_name)
      );

      // Use an identifier string that contains backslash, but is not an
      // existing class.
      $job_store->destruct();
      $job_store = $job_store_factory->getInstance('test\\thisshouldneverbeaclassname');
      $this->assertEquals(
        'jobstore_2087357147_thisshouldneverbeaclassname',
        $table_name = $ref_get_table_name->invoke($job_store)
      );
      $this->assertEquals(0, $job_store->count());
      $this->assertTrue(
        $db->schema()->tableExists($table_name)
      );

      // And finally, use an identifier string that doesn't look like a class
      // name.
      $identifier = 'testthisshouldneverbeaclassname';
      $job_store->destruct();
      $job_store = $job_store_factory->getInstance($identifier);
      $this->assertEquals(
        'jobstore_' . $identifier,
        $table_name = $ref_get_table_name->invoke($job_store)
      );
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
