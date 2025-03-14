<?php

declare(strict_types=1);

namespace Drupal\dkan\Functional;

use Drupal\Core\Database\Database;
use Drupal\Core\Logger\RfcLogLevel;
use Drupal\FunctionalTests\Update\UpdatePathTestBase;

/**
 * Tests update functions for the Database Logging module.
 *
 * @group dblog
 */
class DkanUpdateTest extends UpdatePathTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setDatabaseDumpFiles() {
    $this->databaseDumpFiles = [
      __DIR__ . '/../../fixtures/update/dkan-2.20.3.php.gz',
    ];
  }

  /**
   * Tests that, after update 10101, the 'wid' column can be a 64-bit integer.
   */
  public function testUpdates(): void {
    $this->runUpdates();
  }
}
