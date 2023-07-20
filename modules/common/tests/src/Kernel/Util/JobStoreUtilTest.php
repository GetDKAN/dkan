<?php

/**
 * We must manage namespaces so that we don't end up with a too-long table name.
 */

namespace Drupal\Tests\common\Kernel\Util {

  use Drupal\common\Storage\JobStore;
  use Drupal\common\Util\JobStoreUtil;
  use Drupal\KernelTests\KernelTestBase;

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
     * @covers ::duplicateJobstoreTablesForClass
     */
    public function testDuplicateJobstoreTablesForClass() {
      // Create both deprecated and non-deprecated table for a jobstore.
      /** @var \Drupal\Core\Database\Connection $db */
      $db = $this->container->get('database');
      /** @var \Drupal\common\Storage\JobStoreFactory $job_store_factory */
      $job_store_factory = $this->container->get('dkan.common.job_store');
      $job_store = $job_store_factory->getInstance(\DkanTestUtilJobSubclass::class);

      // First, get the table name without deprecation.
      $ref_get_table_name = new \ReflectionMethod($job_store, 'getHashedTableName');
      $ref_get_table_name->setAccessible(TRUE);
      $table_name = $ref_get_table_name->invoke($job_store);

      // Make the non-deprecated table.
      $this->assertFalse($db->schema()->tableExists($table_name));
      $this->assertEquals(0, $job_store->count());

      // Use the deprecated table name.
      $ref_get_deprecated_table_name = new \ReflectionMethod($job_store, 'getDeprecatedTableName');
      $ref_get_deprecated_table_name->setAccessible(TRUE);
      $deprecated_table_name = $ref_get_deprecated_table_name->invoke($job_store);
      $ref_table_name = new \ReflectionProperty($job_store, 'tableName');
      $ref_table_name->setAccessible(TRUE);
      $ref_table_name->setValue($job_store, $deprecated_table_name);

      // Make the deprecated table.
      $this->assertFalse($db->schema()->tableExists($deprecated_table_name));
      $this->assertEquals(0, $job_store->count());

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
      $job_store_util = new JobStoreUtil($db);
      $this->assertEquals(
        $deprecated_table_name,
        $job_store_util->getDeprecatedTableNameForClassname(\DkanTestUtilJobSubclass::class)
      );
      $this->assertEquals(
        $table_name,
        $job_store_util->getTableNameForClassname(\DkanTestUtilJobSubclass::class)
      );
      $this->assertTrue(
        $job_store_util->duplicateJobstoreTablesForClass(\DkanTestUtilJobSubclass::class)
      );
    }

    /**
     * @covers ::getAllJobstoreTables
     */
    public function testGetGetAllJobstoreTables() {
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
      // Ask util if it found the table.
      $util = new JobStoreUtil($this->container->get('database'));

      $this->assertEquals(
        [
          'jobstore_1885897830_dkantestutiljobsubclass' => 'jobstore_1885897830_dkantestutiljobsubclass',
          'jobstore_3195278052_dkantestutiljobsubclass2' => 'jobstore_3195278052_dkantestutiljobsubclass2',
        ],
        $util->getAllJobstoreTables()
      );
    }

    /**
     * @covers ::getAllTableNamesForClassname
     */
    public function testGetAllTableNamesForClassname() {
      $util = new JobStoreUtil($this->container->get('database'));
      $this->assertEquals(
        ['jobstore_433685385_thingie', 'jobstore__drupal_common_thingie'],
        $util->getAllTableNamesForClassname('\Drupal\common\Thingie')
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
