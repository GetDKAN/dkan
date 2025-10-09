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

  const ROWS_LIMIT = 1000;
  const TRIGGERING_PROPERTIES = ['modified' => 'modified'];
  const MAX_AGE = 3601;

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

    // Enable optional modules.
    \Drupal::service('module_installer')->install(['metastore_facets']);
    \Drupal::service('module_installer')->install(['datastore_mysql_import']);

    $commonConfig = \Drupal::configFactory()->getEditable('common.settings');
    $this->assertFalse($commonConfig->get('always_use_existing_local_perspective'));
    // Set always_use_existing_local_perspective to TRUE to ensure it survives
    // the update.
    $commonConfig->set('always_use_existing_local_perspective', TRUE);
    $commonConfig->save();

    // Change some datastore config, to ensure copied correctly.
    $datastoreConfig = \Drupal::configFactory()->getEditable('datastore.settings');
    $datastoreConfig->set('rows_limit', self::ROWS_LIMIT);
    $datastoreConfig->set('purge_file', TRUE);
    $datastoreConfig->set('purge_table', TRUE);
    $datastoreConfig->set('triggering_properties', self::TRIGGERING_PROPERTIES);
    $datastoreConfig->set('delete_local_resource', TRUE);
    $datastoreConfig->set('drop_datastore_on_post_import_error', TRUE);
    $datastoreConfig->set('response_stream_max_age', self::MAX_AGE);
    $datastoreConfig->save();

    // Change some mysql import config, to ensure copied correctly.
    $mysqlImportConfig = \Drupal::configFactory()->getEditable('datastore_mysql_import.settings');
    $mysqlImportConfig->set('remove_empty_rows', TRUE);
    $mysqlImportConfig->set('strict_mode_disabled', TRUE);
    $mysqlImportConfig->save();

    $this->runUpdates();

    // Assert transitional modules are now installed.
    $this->assertTrue(\Drupal::moduleHandler()->moduleExists('dkan_common'));
    $this->assertTrue(\Drupal::moduleHandler()->moduleExists('dkan_metastore'));
    $this->assertTrue(\Drupal::moduleHandler()->moduleExists('dkan_metastore_search'));
    $this->assertTrue(\Drupal::moduleHandler()->moduleExists('dkan_metastore_facets'));
    $this->assertTrue(\Drupal::moduleHandler()->moduleExists('dkan_metastore_admin'));
    $this->assertTrue(\Drupal::moduleHandler()->moduleExists('dkan_datastore'));
    $this->assertTrue(\Drupal::moduleHandler()->moduleExists('dkan_datastore_mysql_import'));

    // Assert common setting copied over.
    $dkanCommonConfig = \Drupal::configFactory()->getEditable('dkan_common.settings');
    $this->assertTrue($dkanCommonConfig->get('always_use_existing_local_perspective'));

    // Assert datastore settings copied over.
    $dkanDatastoreConfig = \Drupal::configFactory()->getEditable('dkan_datastore.settings');
    $this->assertSame(self::ROWS_LIMIT, $dkanDatastoreConfig->get('rows_limit'));
    $this->assertTrue($dkanDatastoreConfig->get('purge_file'));
    $this->assertTrue($dkanDatastoreConfig->get('purge_table'));
    $this->assertSame(self::TRIGGERING_PROPERTIES, $dkanDatastoreConfig->get('triggering_properties'));
    $this->assertTrue($dkanDatastoreConfig->get('delete_local_resource'));
    $this->assertTrue($dkanDatastoreConfig->get('drop_datastore_on_post_import_error'));
    $this->assertSame(self::MAX_AGE, $dkanDatastoreConfig->get('response_stream_max_age'));

    // Assert mysql import settings copied over.
    $dkanMysqlImportConfig = \Drupal::configFactory()->getEditable('dkan_datastore_mysql_import.settings');
    $this->assertTrue($dkanMysqlImportConfig->get('remove_empty_rows'));
    $this->assertTrue($dkanMysqlImportConfig->get('strict_mode_disabled'));

  }

}
