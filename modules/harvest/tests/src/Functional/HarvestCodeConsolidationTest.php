<?php

declare(strict_types=1);

namespace Drupal\datastore\Functional;

use Drupal\FunctionalTests\Update\UpdatePathTestBase;

/**
 * Tests update functions for the datastore module.
 *
 * @group datastore
 * @group update
 * @group functional3
 */
class HarvestCodeConsolidationTest extends UpdatePathTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setDatabaseDumpFiles() {
    $this->databaseDumpFiles = [
      __DIR__ . '/../../../../../tests/fixtures/update/update-2.20.0.php.gz',
    ];
  }

  /**
   * Test datastore module update 10001.
   */
  public function testUpdates10001on(): void {
    /** @var \Drupal\sample_content\SampleContentService $sample_content_service */
    $sample_content_service = \Drupal::service('dkan.sample_content.service');
    // Create the JSON file in case it doesn't exist.
    $sample_content_service->createDatasetJsonFileFromTemplate();

    // HarvestRun() should fail before update because harvest library
    // namespaces are included in class names stored in the database.
    /** @var \Drupal\harvest\HarvestService $harvest_service */
    $harvest_service = \Drupal::service('dkan.harvest.service');
    try {
      $harvest_service->runHarvest('sample_content');
    }
    catch (\Exception $exception) {
      $exception_msg = $exception->getMessage();
    }
    $this->assertSame('No items found to extract, review your harvest plan.', $exception_msg);

    // Run update and recreate harvest service with updated data.
    $this->runUpdates();
    $harvest_service = \Drupal::service('dkan.harvest.service');

    $result = $harvest_service->runHarvest('sample_content');
    $this->assertIsArray($result);
    $this->assertSame('SUCCESS', $result['status']['extract']);
  }

}
