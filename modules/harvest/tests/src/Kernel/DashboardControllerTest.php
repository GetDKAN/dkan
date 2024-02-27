<?php

namespace Drupal\Tests\harvest\Kernel;

use Drupal\harvest\DashboardController;
use Drupal\KernelTests\KernelTestBase;
use Harvest\ETL\Extract\DataJson;
use Harvest\ETL\Load\Simple;

/**
 * @covers \Drupal\harvest\DashboardController
 * @coversDefaultClass \Drupal\harvest\DashboardController
 *
 * @group dkan
 * @group harvest
 * @group kernel
 */
class DashboardControllerTest extends KernelTestBase {

  protected static $modules = [
    'common',
    'harvest',
    'metastore',
  ];

  protected function setUp() : void {
    parent::setUp();
    $this->installEntitySchema('harvest_run');
  }

  public function testRegisteredPlan() {
    /** @var \Drupal\harvest\HarvestService $harvest_service */
    $harvest_service = $this->container->get('dkan.harvest.service');

    $dashboard_controller = DashboardController::create($this->container);

    // There are no registered harvests, so there should be zero rows. We also
    // verify the empty table value.
    $render_array = $dashboard_controller->harvests();
    $this->assertCount(0, $render_array['#rows'] ?? NULL);
    $this->assertEquals('No harvests found', $render_array['#empty'] ?? NULL);

    // Register a harvest plan but don't run it...
    $plan_identifier = 'test_plan';
    $plan = (object) [
      'identifier' => $plan_identifier,
      'extract' => (object) [
        'type' => DataJson::class,
        'uri' => 'file://' . __DIR__ . '/../../files/data.json',
      ],
      'transforms' => [],
      'load' => (object) [
        'type' => Simple::class,
      ],
    ];
    $this->assertNotNull($harvest_service->registerHarvest($plan));

    // Revisit the dashboard. It should show the registered harvest.
    $render_array = $dashboard_controller->harvests();
    $this->assertCount(1, $render_array['#rows'] ?? NULL);
    $this->assertNotNull($row = $render_array['#rows'][0] ?? NULL);
    /** @var \Drupal\Core\Link $link */
    $this->assertNotNull($link = $row['harvest_link'] ?? NULL);
    $this->assertEquals($plan_identifier, $link->getText());
    // Harvest was registered, but never run.
    $this->assertEquals('REGISTERED', $row['extract_status']['data'] ?? NULL);
    $this->assertEquals('never', $row['last_run'] ?? NULL);
    $this->assertEquals('unknown', $row['dataset_count'] ?? NULL);
  }

}
