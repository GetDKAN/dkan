<?php

declare(strict_types=1);

namespace Drupal\dkan_common\Functional;

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
      dirname(__DIR__, 5) . '/tests/fixtures/update/update-2.20.0.php.gz',
    ];
  }

  /**
   * Test metastore module update 8010.
   */
  public function testRenameUpdate(): void {
    $this->drupalGet('<front>');
    $this->assertSession()->pageTextContains('Log in');

    $config = \Drupal::configFactory()->getEditable('common.settings');
    $this->assertFalse($config->get('always_use_existing_local_perspective'));
    // Assert common module is installed
    $this->assertTrue(\Drupal::moduleHandler()->moduleExists('common'));

    // Set always_use_existing_local_perspective to TRUE to ensure it survives
    // the update.
    $config->set('always_use_existing_local_perspective', TRUE);
    $config->save();

    // Get a baseline for pre-8010.
    $this->assertTrue($config->get('always_use_existing_local_perspective'));

    \Drupal::service('module_installer')->install(['dkan_common']);
    $this->runUpdates();

    // Confirm common is disabled
    $this->assertFalse(\Drupal::moduleHandler()->moduleExists('common'));
    // Assert setting copied over
    $newConfig = \Drupal::configFactory()->getEditable('dkan_common.settings');
    $this->assertTrue($newConfig->get('always_use_existing_local_perspective'));

  }

}
