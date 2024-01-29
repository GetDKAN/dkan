<?php

namespace Drupal\Tests\harvest\Kernel;

use Drupal\harvest\HarvestPlanListBuilder;
use Drupal\KernelTests\KernelTestBase;
use Harvest\ETL\Extract\DataJson;
use Harvest\ETL\Load\Simple;

/**
 * @covers \Drupal\harvest\HarvestPlanListBuilder
 * @coversDefaultClass \Drupal\harvest\HarvestPlanListBuilder
 *
 * @group dkan
 * @group harvest
 * @group kernel
 */
class HarvestPlanListBuilderTest extends KernelTestBase {

  protected static $modules = [
    'common',
    'harvest',
    'metastore',
  ];

  protected function setUp() : void {
    parent::setUp();
    $this->installEntitySchema('harvest_plan');
  }

  public function testRegisteredPlan() {
    /** @var \Drupal\harvest\HarvestService $harvest_service */
    $harvest_service = $this->container->get('dkan.harvest.service');
    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager */
    $entity_type_manager = $this->container->get('entity_type.manager');

    $list_builder = HarvestPlanListBuilder::createInstance(
      $this->container,
      $entity_type_manager->getDefinition('harvest_plan')
    );

    // There are no registered harvests, so there should be zero rows. We also
    // verify the empty table value.
    $table_render = $list_builder->render()['table']['table'] ?? 'phail';
    $this->assertCount(0, $table_render['#rows'] ?? NULL);
    $this->assertEquals('There are no harvest plans yet.', $table_render['#empty'] ?? NULL);

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
    $table_render = $list_builder->render()['table']['table'] ?? 'phail';
    $this->assertCount(1, $table_render['#rows'] ?? NULL);
    $this->assertNotNull($row = $table_render['#rows'][$plan_identifier] ?? 'phail');
    /** @var \Drupal\Core\Link $link */
    $this->assertNotNull($link = $row['harvest_link'] ?? NULL);
    $this->assertEquals($plan_identifier, $link->getText());
    // Harvest was registered, but never run.
    $this->assertEquals('REGISTERED', $row['extract_status']['data'] ?? NULL);
    $this->assertEquals('never', $row['last_run'] ?? NULL);
    $this->assertEquals('unknown', $row['dataset_count'] ?? NULL);
  }

}
