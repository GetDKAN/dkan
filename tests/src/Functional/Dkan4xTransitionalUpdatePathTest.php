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
class Dkan4xTransitionalUpdatePathTest extends UpdatePathTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setDatabaseDumpFiles() {
    $this->databaseDumpFiles = [
      dirname(__DIR__, 2) . '/fixtures/update/update-2.20.0.php.gz',
    ];
  }

  /**
   * Test that common settings are copied to dkan_common on install.
   */
  public function test4xTransitionUpdates(): void {
    // Drop cache container file to avoid issues with module moving.
    \Drupal::database()->truncate('cache_container')->execute();

    $this->drupalGet('<front>');
    $this->assertSession()->pageTextContains('Log in');

    $commonConfig = \Drupal::configFactory()->getEditable('common.settings');
    $this->assertFalse($commonConfig->get('always_use_existing_local_perspective'));
    // Set always_use_existing_local_perspective to TRUE to ensure it survives
    // the update.
    $commonConfig->set('always_use_existing_local_perspective', TRUE);
    $commonConfig->save();

    // Enable the metastore_facets module
    \Drupal::service('module_installer')->install(['metastore_facets']);

    // Get a baseline for pre-install.
    $this->assertTrue($commonConfig->get('always_use_existing_local_perspective'));
    $this->assertTrue(\Drupal::moduleHandler()->moduleExists('metastore_facets'));

    $this->runUpdates();

    // Assert transitional modules are now installed.
    $this->assertTrue(\Drupal::moduleHandler()->moduleExists('dkan_common'));
    $this->assertTrue(\Drupal::moduleHandler()->moduleExists('dkan_metastore'));
    $this->assertTrue(\Drupal::moduleHandler()->moduleExists('dkan_metastore_search'));
    $this->assertTrue(\Drupal::moduleHandler()->moduleExists('dkan_metastore_facets'));
    $this->assertTrue(\Drupal::moduleHandler()->moduleExists('dkan_metastore_admin'));

    // Assert common setting copied over.
    $dkanCommonConfig = \Drupal::configFactory()->getEditable('dkan_common.settings');
    $this->assertTrue($dkanCommonConfig->get('always_use_existing_local_perspective'));

  }

}
