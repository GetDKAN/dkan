<?php

declare(strict_types=1);

namespace Drupal\Tests\dkan_common\Functional;

use Drupal\dkan_harvest\ETL\Extract\DataJson;
use Drupal\dkan_harvest\Load\Dataset;
use Drupal\dkan_harvest\Transform\ResourceImporter;
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
    // If Drupal core version is 11.3 or higher, use the 11.3 dump file.
    // Otherwise, use the 10.5 dump file.
    if (version_compare(\Drupal::VERSION, '11.3.0', '>=')) {
      $this->databaseDumpFiles = [
        dirname(__DIR__, 2) . '/fixtures/update/update-2.x-transition-11.3.php.gz',
      ];
    }
    else {
      $this->databaseDumpFiles = [
        dirname(__DIR__, 2) . '/fixtures/update/update-2.x-transition-10.5.php.gz',
      ];
    }
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
    $this->assertTrue(\Drupal::moduleHandler()->moduleExists('sample_content'));
    $this->assertTrue(\Drupal::moduleHandler()->moduleExists('json_form_widget'));

    $this->assertTrue(\Drupal::moduleHandler()->moduleExists('dkan_common'));
    $this->assertTrue(\Drupal::moduleHandler()->moduleExists('dkan_metastore'));
    $this->assertTrue(\Drupal::moduleHandler()->moduleExists('dkan_metastore_search'));
    $this->assertTrue(\Drupal::moduleHandler()->moduleExists('dkan_metastore_facets'));
    $this->assertTrue(\Drupal::moduleHandler()->moduleExists('dkan_metastore_admin'));
    $this->assertTrue(\Drupal::moduleHandler()->moduleExists('dkan_datastore'));
    $this->assertTrue(\Drupal::moduleHandler()->moduleExists('dkan_datastore_mysql_import'));
    $this->assertTrue(\Drupal::moduleHandler()->moduleExists('dkan_harvest'));
    $this->assertTrue(\Drupal::moduleHandler()->moduleExists('dkan_sample_content'));
    $this->assertTrue(\Drupal::moduleHandler()->moduleExists('dkan_data_dictionary_widget'));

    $this->fixHarvestPlanJsonEscapes();

    // Run all updates.
    $this->runUpdates();

    // Assert legacy modules are now uninstalled and their config removed.
    $this->assertFalse(\Drupal::moduleHandler()->moduleExists('common'));
    $this->assertFalse(\Drupal::moduleHandler()->moduleExists('metastore'));
    $this->assertFalse(\Drupal::moduleHandler()->moduleExists('metastore_admin'));
    $this->assertFalse(\Drupal::moduleHandler()->moduleExists('metastore_search'));
    $this->assertFalse(\Drupal::moduleHandler()->moduleExists('metastore_facets'));
    $this->assertFalse(\Drupal::moduleHandler()->moduleExists('datastore'));
    $this->assertFalse(\Drupal::moduleHandler()->moduleExists('datastore_mysql_import'));
    $this->assertFalse(\Drupal::moduleHandler()->moduleExists('sample_content'));
    $this->assertFalse(\Drupal::moduleHandler()->moduleExists('data_dictionary_widget'));

    $this->assertTrue(\Drupal::moduleHandler()->moduleExists('json_form_widget'));

    $config = \Drupal::configFactory()->getEditable('core.extension');
    $modules = $config->get('module');
    $this->assertArrayNotHasKey('common', $modules);
    $this->assertArrayNotHasKey('metastore', $modules);

    $this->assertEmpty(\Drupal::configFactory()->get('common.settings')->getRawData());
    $this->assertEmpty(\Drupal::configFactory()->get('metastore.settings')->getRawData());
    $this->assertEmpty(\Drupal::configFactory()->get('datastore.settings')->getRawData());

    // Misc checks to ensure config was migrated properly.
    // Open /node/add/data and check that metastore fields exist.
    $this->drupalLogin($this->rootUser);
    $this->drupalGet('node/add/data');
    $this->assertSession()->fieldExists('edit-field-json-metadata-0-value-title');
    $this->assertSession()->fieldExists('edit-field-json-metadata-0-value-description');

    // Add a data dictionary and make sure form loads.
    $this->drupalGet('node/add/data', ['query' => ['schema' => 'data-dictionary']]);
    $this->assertSession()->fieldExists('edit-field-json-metadata-0-title');
    $this->assertSession()->buttonExists('edit-field-json-metadata-0-dictionary-fields-add-row-button');

    // Visit /admin/dkan/datasets and check that the page loads, contains a
    // table with headers including "Title" and "Data Type".
    $this->drupalGet('admin/dkan/datasets');
    $this->assertSession()->elementTextContains('css', 'h1', 'DKAN Metastore (Datasets)');
    $this->assertSession()->elementExists('css', 'th a:contains("Title")');
    $this->assertSession()->elementExists('css', 'th a:contains("Data Type")');

    // Check if harvest plan fixed with correct namespaces.
    $harvest_service = \Drupal::service('dkan.harvest.service');
    $updated_plan = $harvest_service->getHarvestPlanObject('sample_content');
    $this->assertEquals("\\" . DataJson::class, $updated_plan->extract->type);
    $this->assertEquals("\\" . ResourceImporter::class, $updated_plan->transforms[0]);
    $this->assertEquals("\\" . Dataset::class, $updated_plan->load->type);
  }

  /**
   * Fix harvest plan JSON escapes.
   *
   * Restoring the DB from a dump will brake our PHP class name backslash
   * escaping.Load all records from the harvest_plans table and modify the data
   * to double any single backslashes.
   */
  private function fixHarvestPlanJsonEscapes(): void {
    $database = \Drupal::database();
    $query = $database->select('harvest_plans', 'hp')
      ->fields('hp', ['id', 'data']);
    $results = $query->execute();
    foreach ($results as $record) {
      $data = $record->data;
      $data = preg_replace('/(?<!\\\\)\\\\/', '\\\\\\\\', $data);
      $database->update('harvest_plans')
        ->fields(['data' => $data])
        ->condition('id', $record->id)
        ->execute();
    }

  }

}
