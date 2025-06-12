<?php

declare(strict_types=1);

namespace Drupal\datastore\Functional;

use Drupal\FunctionalTests\Update\UpdatePathTestBase;

/**
 * Tests update functions for the datastore module.
 *
 * @group datastore
 * @group update
 * @group dkan
 * @group functional3
 */
class DatastoreUpdatePathTest extends UpdatePathTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setDatabaseDumpFiles() {
    $this->databaseDumpFiles = [
      __DIR__ . '/../../../../../tests/fixtures/update/update-2.20.0.php.gz',
    ];
  }

  /**
   * Test datastore module updates 9003, 9005.
   */
  public function testUpdates9003on(): void {
    $schema = \Drupal::database()->schema();
    $config = \Drupal::configFactory()->getEditable('datastore.settings');

    // Get a baseline for pre-9003.
    $table = 'dkan_post_import_job_status';
    $this->assertFalse($schema->fieldExists($table, 'id'));
    $this->assertFalse($schema->fieldExists($table, 'timestamp'));

    // Get baseline for pre-9005
    $this->assertSame(1, $config->get('purge_file'));
    $this->assertSame(1, $config->get('purge_table'));

    $this->runUpdates();
    $schema = \Drupal::database()->schema();
    $config = \Drupal::configFactory()->getEditable('datastore.settings');

    // Confirm results of update 9003.
    $table = 'dkan_post_import_job_status';
    $this->assertTrue($schema->fieldExists($table, 'id'));
    $this->assertTrue($schema->fieldExists($table, 'timestamp'));

    // Confirm results of update 9005 (purge settings now bools).
    $this->assertSame(TRUE, $config->get('purge_file'));
    $this->assertSame(TRUE, $config->get('purge_table'));
  }
}
