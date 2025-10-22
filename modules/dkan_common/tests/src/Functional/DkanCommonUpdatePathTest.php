<?php

declare(strict_types=1);

namespace Drupal\Tests\dkan_common\Functional;

use Drupal\FunctionalTests\Update\UpdatePathTestBase;

/**
 * Tests update functions for the metastore module.
 *
 * @group metastore
 * @group update
 * @group functional3
 */
class DkanCommonUpdatePathTest extends UpdatePathTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setDatabaseDumpFiles() {
    $this->databaseDumpFiles = [
      dirname(__DIR__, 5) . '/tests/fixtures/update/update-3.x-dev.php.gz',
    ];
  }

  /**
   * Test dkan_common module update 10000.
   */
  public function testRenameUpdate(): void {
    // Drop cache container file to avoid issues with module moving.
    // \Drupal::database()->truncate('cache_container')->execute();

    // Make sure site isn't broken before update.
    $this->drupalGet('<front>');
    $this->assertSession()->pageTextContains('Log in');

    // Get a baseline for pre-10000. Check if exists in extension config.
    $config = \Drupal::configFactory()->getEditable('core.extension');
    $modules = $config->get('module');
    $this->assertArrayHasKey('common', $modules);
    // Assert common settings config exists.
    $this->assertNotEmpty(\Drupal::configFactory()->get('common.settings')->getRawData());

    $this->runUpdates();

    // Confirm common is disabled
    $this->assertFalse(\Drupal::moduleHandler()->moduleExists('common'));
    // Confirm common now gone from extension config.
    $config = \Drupal::configFactory()->getEditable('core.extension');
    $modules = $config->get('module');
    $this->assertArrayNotHasKey('common', $modules);
    // Confirm common settings config has been deleted.
    $this->assertEmpty(\Drupal::configFactory()->get('common.settings')->getRawData());
  }

}
