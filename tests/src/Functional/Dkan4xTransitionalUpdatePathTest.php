<?php

declare(strict_types=1);

namespace Drupal\Tests\dkan_common\Functional;

<<<<<<< HEAD
use Composer\Semver\VersionParser;
use Drupal\FunctionalTests\Update\UpdatePathTestBase;


=======
use Drupal\FunctionalTests\Update\UpdatePathTestBase;

>>>>>>> c5e721447 (Add transitional modules and other updates)
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
    $this->drupalGet('<front>');
    $this->assertSession()->pageTextContains('Log in');

    $parser = new VersionParser();
    $constraint = $parser->parseConstraints('^11.3.0');
    $optional = ['metastore_facets', 'datastore_mysql_import', 'sample_content'];

    if ($constraint->matches($parser->parseConstraints(\Drupal::VERSION))) {
      // On Drupal ^11.3, it doesn't work to enable more modules before updb.
      $this->runUpdates();
      \Drupal::service('module_installer')->install($optional);
    }
    else {
      // We want to test having the modules on first on at least some builds.
      \Drupal::service('module_installer')->install($optional);
      $this->runUpdates();

    }

    // Assert transitional modules are now installed.
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
  }

}
