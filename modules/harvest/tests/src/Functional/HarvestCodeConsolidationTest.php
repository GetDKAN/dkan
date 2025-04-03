<?php

declare(strict_types=1);

namespace Drupal\datastore\Functional;

use Drupal\FunctionalTests\Update\UpdatePathTestBase;
use Drupal\harvest\HarvestService;
use Drupal\sample_content\SampleContentService;
use PHPUnit\Exception;

/**
 * Tests update functions for the datastore module.
 *
 * @group datastore
 * @group update
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

    // Set up sample content.
    /** @var SampleContentService $sample_service */
//    $sample_service = \Drupal::service('dkan.sample_content.service');
//    $sample_service->registerSampleContentHarvest('sample_content');



    // Get a baseline for pre-10001.
    /** @var HarvestService $harvest_service */
    $harvest_service = \Drupal::service('dkan.harvest.service');
    try {
      $result = $harvest_service->runHarvest('sample_content');
    }
    catch (\Exception $exception) {
      $result = $exception->getMessage();
    }
    $this->assertSame('No items found to extract, review your harvest plan.', $result);

    // Run update and recreate harvest service with updated data.
    $this->runUpdates();
    $harvest_service = \Drupal::service('dkan.harvest.service');


    try {
      $result = $harvest_service->runHarvest('sample_content');
    }
    catch (\Exception $exception) {
      $result = $exception->getMessage();
    }
    $this->assertIsArray($result);
    $this->assertSame('SUCCESS', $result['status']['extract']);
  }
}
