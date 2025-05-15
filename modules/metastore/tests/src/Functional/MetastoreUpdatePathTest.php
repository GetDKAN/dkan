<?php

declare(strict_types=1);

namespace Drupal\metastore\Functional;

use Drupal\FunctionalTests\Update\UpdatePathTestBase;

/**
 * Tests update functions for the metastore module.
 *
 * @group metastore
 * @group update
 */
class MetastoreUpdatePathTest extends UpdatePathTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setDatabaseDumpFiles() {
    $this->databaseDumpFiles = [
      __DIR__ . '/../../../../../tests/fixtures/update/update-2.20.0.php.gz',
    ];
  }

  /**
   * Test metastore module update 8010.
   */
  public function testUpdates8010on(): void {
    $config = \Drupal::configFactory()->getEditable('metastore.settings');

    // Get a baseline for pre-8010.
    $this->assertNull($config->get('redirect_to_datasets'));

    $this->runUpdates();

    // Confirm results of update 8010.
    $config = \Drupal::configFactory()->getEditable('metastore.settings');
    $this->assertTrue($config->get('redirect_to_datasets'));
  }
}
