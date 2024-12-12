<?php

namespace Drupal\Tests\harvest\Kernel;

use Drupal\harvest\HarvestPlanListBuilder;
use Drupal\harvest\HarvestService;
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

  const HARVEST_HEADERS = [
    'Harvest ID',
    'Extract Status',
    'Last Run',
    '# of Datasets',
  ];

  protected static $modules = [
    'common',
    'harvest',
    'metastore',
    'node',
    'user',
  ];

  protected function setUp() : void {
    parent::setUp();
    $this->installEntitySchema('harvest_plan');
    $this->installEntitySchema('harvest_hash');
    $this->installEntitySchema('harvest_run');
    $this->installEntitySchema('node');
  }

  public function testNoHarvests() {
    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager */
    $entity_type_manager = $this->container->get('entity_type.manager');

    $list_builder = HarvestPlanListBuilder::createInstance(
      $this->container,
      $entity_type_manager->getDefinition('harvest_plan')
    );

    $response = $list_builder->render();
    $json = json_encode($response);

    $strings = array_merge(self::HARVEST_HEADERS, [
      'There are no harvest plans yet.',
    ]);

    foreach ($strings as $string) {
      $this->assertStringContainsString($string, $json);
    }
  }

  public function testRegisteredHarvest() {
    /** @var \Drupal\harvest\HarvestService $harvest_service */
    $harvest_service = $this->container->get('dkan.harvest.service');
    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager */
    $entity_type_manager = $this->container->get('entity_type.manager');

    $list_builder = HarvestPlanListBuilder::createInstance(
      $this->container,
      $entity_type_manager->getDefinition('harvest_plan')
    );

    // Register a harvest but don't run it.
    $this->registerHarvestPlan($harvest_service);

    $response = $list_builder->render();

    $json = json_encode($response);

    foreach (self::HARVEST_HEADERS as $string) {
      $this->assertStringContainsString($string, $json);
    }
  }

  public function testGoodHarvestRun() {
    /** @var \Drupal\harvest\HarvestService $harvest_service */
    $harvest_service = $this->container->get('dkan.harvest.service');
    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager */
    $entity_type_manager = $this->container->get('entity_type.manager');

    $list_builder = HarvestPlanListBuilder::createInstance(
      $this->container,
      $entity_type_manager->getDefinition('harvest_plan')
    );

    // Run the harvest.
    $plan_id = 'run_harvest';
    $this->registerHarvestPlan($harvest_service, $plan_id);
    $run_result = $harvest_service->runHarvest($plan_id);
    $this->assertEquals('SUCCESS', $run_result['status']['extract'] ?? 'not_a_successful_run');

    $response = $list_builder->render();

    $json = json_encode($response);
    $strings = array_merge(self::HARVEST_HEADERS, [
      'harvest_link',
      'SUCCESS',
      json_encode(date('m/d/y H:m:s T', $run_result['identifier'])),
      '2',
    ]);
    foreach ($strings as $string) {
      $this->assertStringContainsString($string, $json);
    }

    // Directly assert the dataset count so the string assert above won't pass
    // by accident.
    $this->assertEquals(
      2,
      $response['table']['#rows']['run_harvest']['dataset_count'] ?? NULL
    );
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
    $table_render = $list_builder->render()['table'] ?? 'phail';
    $this->assertCount(0, $table_render['#rows'] ?? NULL);
    $this->assertEquals('There are no harvest plans yet.', $table_render['#empty'] ?? NULL);

    // Register a harvest plan but don't run it...
    $plan_identifier = 'test_plan';
    $this->registerHarvestPlan($harvest_service, $plan_identifier);

    // Revisit the dashboard. It should show the registered harvest.
    $table_render = $list_builder->render()['table'] ?? 'phail';
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

  protected function registerHarvestPlan(HarvestService $harvest_service, string $plan_identifier = 'test_plan') {
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
    $this->assertNotNull($identifier = $harvest_service->registerHarvest($plan));
    return $identifier;
  }

}
