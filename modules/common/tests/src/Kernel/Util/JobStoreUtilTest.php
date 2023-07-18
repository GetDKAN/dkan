<?php

/**
 * We must manage namespaces so that we don't end up with a too-long table name.
 */

namespace Drupal\Tests\common\Kernel\Storage {

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

    public function testTables() {
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
          'jobstore_1885897830_dkantestutiljobsubclass',
          'jobstore_3195278052_dkantestutiljobsubclass2',
        ],
        $util->getAllJobstoreTables()
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
