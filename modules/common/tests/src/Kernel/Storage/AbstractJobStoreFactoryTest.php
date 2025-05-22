<?php

/**
 * Manage namespaces so they don't get too long for deprecated table names.
 */

namespace Drupal\Tests\common\Kernel\Storage {

  use Drupal\common\Storage\JobStore;
  use Drupal\KernelTests\KernelTestBase;

  /**
   * @covers \Drupal\common\Storage\AbstractJobStoreFactory
   * @coversDefaultClass \Drupal\common\Storage\AbstractJobStoreFactory
   *
   * @group dkan
   * @group common
   * @group kernel
   *
   * @see \Drupal\Tests\common\Kernel\Storage\JobStoreFactoryTest
   */
  class AbstractJobStoreFactoryTest extends KernelTestBase {

    protected static $modules = [
      'common',
    ];

    public function testDeprecatedClassnameTable() {
      $db = $this->container->get('database');
      $eventDispatcher = $this->container->get('event_dispatcher');
      // Make a concrete AbstractJobStoreFactory object.
      /** @var \DkanTestConcreteAbstractJobStoreFactory $job_store_factory */
      $job_store_factory = new \DkanTestConcreteAbstractJobStoreFactory($db, $eventDispatcher);
      // Get a Job object by specifying an instance name.
      $job_store = $job_store_factory->getInstance(\DkanTestConcreteJobSubclass::class);

      $this->assertInstanceOf(JobStore::class, $job_store);

      // First, get the table name without deprecation by calculating a hash.
      $ref_get_table_name = new \ReflectionMethod($job_store, 'getTableName');
      $ref_get_table_name->setAccessible(TRUE);
      $table_name = $ref_get_table_name->invoke($job_store);
      $this->assertEquals('jobstore_580088250_dkantestconcretejobsubclass', $table_name);

      // This table does not exist.
      $this->assertFalse(
        $db->schema()->tableExists($table_name)
      );

      // Use the deprecated table name.
      $ref_get_deprecated_table_name = new \ReflectionMethod($job_store_factory, 'getDeprecatedTableName');
      $ref_get_deprecated_table_name->setAccessible(TRUE);
      $deprecated_table_name = $ref_get_deprecated_table_name->invokeArgs(
        $job_store_factory,
        [\DkanTestConcreteJobSubclass::class]
      );
      $this->assertEquals('jobstore_dkantestconcretejobsubclass', $deprecated_table_name);
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
      $job_store = $job_store_factory->getInstance(\DkanTestConcreteJobSubclass::class);
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
      $job_store = $job_store_factory->getInstance(\DkanTestConcreteJobSubclass::class);
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

      // Use an identifier string that doesn't look like a class name.
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

      // And finally, the right way. We create an instance without specifying
      // an identifier, so that the factory uses its default.
      $job_store->destruct();
      $job_store = $job_store_factory->getInstance();
      $this->assertEquals(
        'concrete_job_store',
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

  use Drupal\common\Storage\AbstractJobStoreFactory;
  use Procrastinator\Job\Job;

  class DkanTestConcreteAbstractJobStoreFactory extends AbstractJobStoreFactory {

    protected string $tableName = 'concrete_job_store';

  }

  class DkanTestConcreteJobSubclass extends Job {

    protected function runIt() {
    }

  }
}
