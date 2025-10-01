<?php

/**
 * We must manage namespaces so that we don't end up with a too-long table name.
 */

namespace Drupal\Tests\common\Kernel\Util {

  use Drupal\common\Storage\JobStore;
  use Drupal\common\Util\JobStoreFactoryAccessor;
  use Drupal\common\Util\JobStoreUtil;
  use Drupal\KernelTests\KernelTestBase;
  use FileFetcher\FileFetcher;

  /**
   * @covers \Drupal\common\Util\JobStoreUtil
   * @coversDefaultClass \Drupal\common\Util\JobStoreUtil
   *
   * @group dkan
   * @group common
   * @group kernel
      */
  class JobStoreUtilTest extends KernelTestBase {

    protected static $modules = [
      'common',
    ];

    /**
     * Create a deprecated jobstore table for the given class name identifier.
     *
     * Defaults to FileFetcher/FileFetcher because that class name is short
     * enough as a deprecated table name to work with test's db prefix.
     */
    protected function deprecatedJobStoreSetup(string $class_name = FileFetcher::class): void {
      /** @var \Drupal\Core\Database\Connection $db */
      $db = $this->container->get('database');
      $eventDispatcher = $this->container->get('event_dispatcher');
      $factory_accessor = new JobStoreFactoryAccessor($db, $eventDispatcher);

      // First, get the non-deprecated table name.
      $table_name = $factory_accessor->accessTableName($class_name);

      // Use the deprecated table name.
      $deprecated_table_name = $factory_accessor->accessDeprecatedTableName($class_name);

      // Make the deprecated table by creating a new jobstore object rather than
      // use the factory, so it does not recompute the table name.
      $this->assertFalse($db->schema()->tableExists($deprecated_table_name));
      $job_store = new JobStore($deprecated_table_name, $db, $eventDispatcher);
      // Count() will create the table.
      $this->assertEquals(0, $job_store->count());

      // Assert that the deprecated table exists but not the non-deprecated.
      $this->assertTrue(
        $db->schema()->tableExists($deprecated_table_name),
        $deprecated_table_name
      );
      $this->assertFalse(
        $db->schema()->tableExists($table_name),
        $table_name
      );
    }

    /**
     * @covers ::getAllDeprecatedJobstoreTableNames
     */
    public function testGetAllDeprecatedJobstoreTableNames() {
      $this->deprecatedJobStoreSetup();
      $job_store_util = new JobStoreUtil($this->container->get('database'), $this->container->get('event_dispatcher'));
      // Should get a list back of only the deprecated name and its class.
      $this->assertEquals(
        ['FileFetcher\FileFetcher' => 'jobstore_filefetcher_filefetcher'],
        $job_store_util->getAllDeprecatedJobstoreTableNames()
      );
    }

    /**
     * @covers ::renameDeprecatedJobstoreTables
     */
    public function testRenameDeprecatedJobstoreTables() {
      $job_store_util = new JobStoreUtil($this->container->get('database'), $this->container->get('event_dispatcher'));
      // Before we set anything up, renaming should result in an empty array.
      $this->assertSame([], $job_store_util->renameDeprecatedJobstoreTables());
      // Set up the tables...
      $this->deprecatedJobStoreSetup();
      // Should get a list with the deprecated changed to new.
      $this->assertEquals(
        ['jobstore_filefetcher_filefetcher' => 'jobstore_524493904_filefetcher'],
        $job_store_util->renameDeprecatedJobstoreTables()
      );
    }

    /**
     * @covers ::duplicateJobstoreTablesForClassname
     */
    public function testDuplicateJobstoreTablesForClass() {
      // Create both deprecated and non-deprecated table for a jobstore.
      /** @var \Drupal\Core\Database\Connection $db */
      $db = $this->container->get('database');
      $eventDispatcher = $this->container->get('event_dispatcher');

      $job_store_factory = new JobStoreFactoryAccessor($db, $eventDispatcher);
      $identifier = \DkanTestUtilJobSubclass::class;

      // First, get the table name without deprecation.
      $table_name = $job_store_factory->accessTableName($identifier);

      // Make the non-deprecated table.
      $table_store = new JobStore($table_name, $db, $eventDispatcher);
      $this->assertFalse($db->schema()->tableExists($table_name));
      $this->assertEquals(0, $table_store->count());

      // Make the deprecated table.
      $deprecated_table_name = $job_store_factory->accessDeprecatedTableName($identifier);
      $deprecated_store = new JobStore($deprecated_table_name, $db, $eventDispatcher);

      $this->assertFalse($db->schema()->tableExists($deprecated_table_name));
      $this->assertEquals(0, $deprecated_store->count());

      // Assert that both the non-deprecated and deprecated tables exist.
      $this->assertTrue(
        $db->schema()->tableExists($deprecated_table_name),
        $deprecated_table_name
      );
      $this->assertTrue(
        $db->schema()->tableExists($table_name),
        $table_name
      );

      // Now that we have both tables, our utility object should find them.
      $job_store_util = new JobStoreUtil($db, $eventDispatcher);
      $this->assertEquals(
        $deprecated_table_name,
        $job_store_util->getDeprecatedTableNameForClassname($identifier)
      );
      $this->assertEquals(
        $table_name,
        $job_store_util->getTableNameForClassname($identifier)
      );
      $this->assertTrue(
        $job_store_util->duplicateJobstoreTablesForClassname($identifier)
      );
    }

    /**
     * @covers ::getAllJobstoreTables
     * @covers ::getUnknownJobstoreTables
     */
    public function testGetAllAndUnknownJobstoreTables() {
      /** @var \Drupal\common\Storage\JobStoreFactory $job_store_factory */
      $job_store_factory = $this->container->get('dkan.common.job_store');
      // Two jobstore objects.
      /** @var \Drupal\common\Storage\JobStore $job_store */
      $job_store = $job_store_factory->getInstance(\DkanTestUtilJobSubclass::class);
      /** @var \Drupal\common\Storage\JobStore $job_store_2 */
      $job_store_2 = $job_store_factory->getInstance(\DkanTestUtilJobSubclass2::class);
      // Create tables using count().
      $this->assertEquals(0, $job_store->count());
      $this->assertEquals(0, $job_store_2->count());
      // Ask util if it found the tables.
      $util = new JobStoreUtil($this->container->get('database'), $this->container->get('event_dispatcher'));
      $tables = [
        'jobstore_1885897830_dkantestutiljobsubclass' => 'jobstore_1885897830_dkantestutiljobsubclass',
        'jobstore_3195278052_dkantestutiljobsubclass2' => 'jobstore_3195278052_dkantestutiljobsubclass2',
      ];
      $this->assertEquals($tables, $util->getAllJobstoreTables());
      // In this circumstance, we get the same value from
      // getUnknownJobstoreTables(), since our job subclasses are not in the
      // fixable list.
      $this->assertEquals($tables, $util->getUnknownJobstoreTables());
      // Drop the tables...
      $job_store->destruct();
      $job_store_2->destruct();
      // Now there should be no job store tables.
      $this->assertSame([], $util->getAllJobstoreTables());
    }

    /**
     * @covers ::getDuplicateJobstoreTables
     * @covers ::reconcileDuplicateJobstoreTables
     * @covers ::reconcileDuplicateJobstoreTable
     */
    public function testReconcileDuplicateJobstoreTable() {
      /** @var \Drupal\Core\Database\Connection $db */
      $db = $this->container->get('database');
      $eventDispatcher = $this->container->get('event_dispatcher');
      $identifier = FileFetcher::class;
      // Key is identifier, value is value.
      $deprecated_data = [
        'a' => '"old a"',
        'b' => '"old b"',
      ];
      $non_deprecated_data = [
        'a' => '"new a"',
      ];

      $job_store_factory = new JobStoreFactoryAccessor($db, $eventDispatcher);

      // Store deprecated job data.
      $deprecated_table_name = $job_store_factory->accessDeprecatedTableName($identifier);
      $deprecated_job = new JobStore($deprecated_table_name, $db, $eventDispatcher);
      foreach ($deprecated_data as $key => $value) {
        $deprecated_job->store($value, $key);
      }

      // Store non-deprecated job data.
      $table_name = $job_store_factory->accessTableName($identifier);
      $table_job = new JobStore($table_name, $db, $eventDispatcher);
      foreach ($non_deprecated_data as $key => $value) {
        $table_job->store($value, $key);
      }

      // Assert both tables.
      $this->assertTrue($db->schema()->tableExists($deprecated_table_name));
      $this->assertTrue($db->schema()->tableExists($table_name));

      // Job store utility.
      $job_store_util = new JobStoreUtil($db, $eventDispatcher);
      // getDuplicateJobstoreTables() should find this becasue FileFetcher is
      // one of our fixable classes.
      $this->assertEquals(
        ['jobstore_filefetcher_filefetcher' => 'jobstore_524493904_filefetcher'],
        $job_store_util->getDuplicateJobstoreTables()
      );
      // Perform the reconciliation.
      $job_store_util->reconcileDuplicateJobstoreTables();

      // Assert only the non-deprecated table exists.
      $this->assertFalse($db->schema()->tableExists($deprecated_table_name));
      $this->assertTrue($db->schema()->tableExists($table_name));

      // Assert the contents.
      $this->assertEquals('"new a"', $table_job->retrieve('a'));
      $this->assertEquals('"old b"', $table_job->retrieve('b'));
    }

    /**
     * @covers ::getDuplicateJobstoreTables
     * @covers ::reconcileDuplicateJobstoreTables
     * @covers ::reconcileDuplicateJobstoreTable
     */
    public function testReconcileDuplicateJobstoreTableNoOverlap() {
      /** @var \Drupal\Core\Database\Connection $db */
      $db = $this->container->get('database');
      $eventDispatcher = $this->container->get('event_dispatcher');
      $identifier = FileFetcher::class;
      // Key is identifier, value is value.
      $deprecated_data = [
        'a' => '"old a"',
        'b' => '"old b"',
      ];
      $non_deprecated_data = [
        'c' => '"new c"',
      ];

      $job_store_factory = new JobStoreFactoryAccessor($db, $eventDispatcher);

      // Store deprecated job data.
      $deprecated_table_name = $job_store_factory->accessDeprecatedTableName($identifier);
      $deprecated_job = new JobStore($deprecated_table_name, $db, $eventDispatcher);
      foreach ($deprecated_data as $key => $value) {
        $deprecated_job->store($value, $key);
      }

      // Store non-deprecated job data.
      $table_name = $job_store_factory->accessTableName($identifier);
      $table_job = new JobStore($table_name, $db, $eventDispatcher);
      foreach ($non_deprecated_data as $key => $value) {
        $table_job->store($value, $key);
      }

      // Assert both tables.
      $this->assertTrue($db->schema()->tableExists($deprecated_table_name));
      $this->assertTrue($db->schema()->tableExists($table_name));

      // Job store utility.
      $job_store_util = new JobStoreUtil($db, $eventDispatcher);
      // getDuplicateJobstoreTables() should find this becasue FileFetcher is
      // one of our fixable classes.
      $this->assertEquals(
        ['jobstore_filefetcher_filefetcher' => 'jobstore_524493904_filefetcher'],
        $job_store_util->getDuplicateJobstoreTables()
      );
      // Perform the reconciliation.
      $job_store_util->reconcileDuplicateJobstoreTables();

      // Assert only the non-deprecated table exists.
      $this->assertFalse($db->schema()->tableExists($deprecated_table_name));
      $this->assertTrue($db->schema()->tableExists($table_name));

      // Assert the contents.
      $this->assertEquals('"old a"', $table_job->retrieve('a'));
      $this->assertEquals('"old b"', $table_job->retrieve('b'));
      $this->assertEquals('"new c"', $table_job->retrieve('c'));
    }

    /**
     * @covers ::keyedToList
     */
    public function testKeyedToList() {
      $job_store_util = new JobStoreUtil($this->container->get('database'), $this->container->get('event_dispatcher'));
      $this->assertEquals(
        [['a', 'b']],
        $job_store_util->keyedToList(['a' => 'b'])
      );
      $this->assertSame(
        [],
        $job_store_util->keyedToList([])
      );
    }

    /**
     * @covers ::keyedToListDecorator
     */
    public function testKeyedToListDecorator() {
      $job_store_util = new JobStoreUtil($this->container->get('database'), $this->container->get('event_dispatcher'));
      $this->assertEquals(
        ['a > b'],
        $job_store_util->keyedToListDecorator(['a' => 'b'], ' > ')
      );
      $this->assertSame(
        [],
        $job_store_util->keyedToListDecorator([], ' > ')
      );
    }

  }
}

namespace {

  use Procrastinator\Job\Job;

  class DkanTestUtilJobSubclass extends Job {

    protected function runIt() {
    }

  }

  class DkanTestUtilJobSubclass2 extends Job {

    protected function runIt() {
    }

  }
}
