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
      dirname(__DIR__, 2) . '/fixtures/update/update-2.x-transition.php.gz',
    ];
  }

  /**
   * Test that common settings are copied to dkan_common on install.
   */
  public function test4xTransitionUpdates(): void {
    $this->drupalGet('<front>');
    $this->assertSession()->pageTextContains('Log in');

    // Ensure legacy modules are still installed.
    $this->assertTrue(\Drupal::moduleHandler()->moduleExists('common'));
    $this->assertTrue(\Drupal::moduleHandler()->moduleExists('metastore'));
    $this->assertTrue(\Drupal::moduleHandler()->moduleExists('metastore_search'));
    $this->assertTrue(\Drupal::moduleHandler()->moduleExists('metastore_admin'));
    $this->assertTrue(\Drupal::moduleHandler()->moduleExists('datastore'));
    $this->assertTrue(\Drupal::moduleHandler()->moduleExists('harvest'));

    // Enable optional modules.
    \Drupal::service('module_installer')->install([
      'metastore_facets',
      'datastore_mysql_import',
      'sample_content',
    ]);

    $this->assertTrue(\Drupal::moduleHandler()->moduleExists('dkan_common'));
    $this->assertTrue(\Drupal::moduleHandler()->moduleExists('dkan_datastore'));
    $this->assertTrue(\Drupal::moduleHandler()->moduleExists('dkan_datastore_mysql_import'));
    $this->assertTrue(\Drupal::moduleHandler()->moduleExists('dkan_harvest'));
    $this->assertTrue(\Drupal::moduleHandler()->moduleExists('dkan_metastore'));
    $this->assertTrue(\Drupal::moduleHandler()->moduleExists('dkan_data_dictionary_widget'));
    $this->assertTrue(\Drupal::moduleHandler()->moduleExists('dkan_metastore_admin'));
    $this->assertTrue(\Drupal::moduleHandler()->moduleExists('dkan_metastore_facets'));
    $this->assertTrue(\Drupal::moduleHandler()->moduleExists('dkan_metastore_search'));
    $this->assertTrue(\Drupal::moduleHandler()->moduleExists('dkan_sample_content'));


    // Run all updates.
    $this->runUpdates();

    // Assert legacy modules are now uninstalled and their config removed.
    $this->assertFalse(\Drupal::moduleHandler()->moduleExists('common'));
    $this->assertFalse(\Drupal::moduleHandler()->moduleExists('metastore'));

    $config = \Drupal::configFactory()->getEditable('core.extension');
    $modules = $config->get('module');
    $this->assertArrayNotHasKey('common', $modules);
    $this->assertArrayNotHasKey('metastore', $modules);

    $this->assertEmpty(\Drupal::configFactory()->get('common.settings')->getRawData());
    $this->assertEmpty(\Drupal::configFactory()->get('metastore.settings')->getRawData());

    // Misc checks to ensure config was migrated properly.
    // Open /node/add/dataset and check that metastore fields exist.
    $this->drupalLogin($this->rootUser);
    $this->drupalGet('node/add/data');
    $this->assertSession()->fieldExists('edit-field-json-metadata-0-value-title');
    $this->assertSession()->fieldExists('edit-field-json-metadata-0-value-description');
  }

}
