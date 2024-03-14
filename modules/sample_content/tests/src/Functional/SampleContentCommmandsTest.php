<?php

namespace Drupal\Tests\sample_content\Functional;

use Drupal\Tests\BrowserTestBase;
use Drush\TestTraits\DrushTestTrait;

/**
 * @coversDefaultClass \Drupal\sample_content\Drush
 *
 * @group dkan
 * @group sample_content
 * @group functional
 */
class SampleContentCommmandsTest extends BrowserTestBase {

  use DrushTestTrait;

  protected $defaultTheme = 'stark';

  protected static $modules = [
    'node',
    'sample_content',
  ];

  public function test() {
    $harvest_plan_name = 'sample_content';

    // Run the create command.
    $this->drush('dkan:sample-content:create');
    $output = $this->getOutput();

    // Start asserting.
    foreach ([
      'run_id',
      'processed',
      'created',
      'updated',
      'errors',
      'sample_content',
      // The number of datasets we expect to create.
      '10',
    ] as $expected) {
      $this->assertStringContainsString($expected, $output);
    }

    // Ask the API.
    /** @var \Drupal\harvest\HarvestService $harvest_service */
    $harvest_service = $this->container->get('dkan.harvest.service');
    $this->assertCount(1, $harvest_service->getAllHarvestIds());
    $this->assertNotNull($harvest_service->getHarvestPlanObject($harvest_plan_name));
    $this->assertNotEmpty($run_id = $harvest_service->getLastHarvestRunId($harvest_plan_name));
    $this->assertNotEmpty(
      $run_info = json_decode($harvest_service->getHarvestRunInfo($harvest_plan_name, $run_id), TRUE)
    );
    $this->assertCount(10, $run_info['status']['extracted_items_ids'] ?? []);

    // Run the remove command.
    $this->drush('dkan:sample-content:remove');
    // Logged output counts as an error, even if it's not an error.
    $output = $this->getErrorOutput();

    // Assert the output.
    foreach ([
      'Reverting harvest plan: ' . $harvest_plan_name,
      'Deregistering harvest plan: ' . $harvest_plan_name,
    ] as $expected) {
      $this->assertStringContainsString($expected, $output);
    }
  }

}
