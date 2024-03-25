<?php

namespace Drupal\Tests\harvest\Kernel\Entity;

use Drupal\harvest\HarvestRunInterface;
use Drupal\KernelTests\KernelTestBase;

/**
 * @covers \Drupal\harvest\Entity\HarvestRunRepository
 * @coversDefaultClass \Drupal\harvest\Entity\HarvestRunRepository
 *
 * @group dkan
 * @group harvest
 * @group kernel
 */
class HarvestRunRepositoryTest extends KernelTestBase {

  protected static $modules = [
    'common',
    'harvest',
    'metastore',
  ];

  protected function setUp() : void {
    parent::setUp();
    // $this->installEntitySchema('harvest_plan');
    //    $this->installEntitySchema('harvest_hash');
    $this->installEntitySchema('harvest_run');
  }

  /**
   * @covers ::destructForPlanId
   */
  public function testDestructForPlanId() {
    $destruct_id = '1711038292';
    $keep_id = '1711038293';
    $destruct_this_plan_id = 'DESTRUCTTHISPLAN';
    $keep_this_plan_id = 'KEEPTHISPLAN';
    $run_data = [
      'plan' => '{"plan": "json"}',
      'status' => [
        'extract' => 'AWESOME',
      ],
    ];
    /** @var \Drupal\harvest\Entity\HarvestRunRepository $harvest_run_repo */
    $harvest_run_repo = $this->container->get('dkan.harvest.storage.harvest_run_repository');
    $run_data['identifier'] = $keep_id;
    $harvest_run_repo->storeRun($run_data, $keep_this_plan_id, $keep_id);
    $run_data['identifier'] = $destruct_id;
    $harvest_run_repo->storeRun($run_data, $destruct_this_plan_id, $destruct_id);

    $harvest_run_repo->destructForPlanId($destruct_this_plan_id);

    $this->assertCount(1, $harvest_run_repo->getUniqueHarvestPlanIds());
    $this->assertCount(1, $harvest_run_repo->retrieveAllRunIds($keep_this_plan_id));
    /** @var \Drupal\harvest\HarvestRunInterface $run */
    $this->assertInstanceOf(
      HarvestRunInterface::class,
      $run = $harvest_run_repo->loadEntity($keep_this_plan_id, $keep_id)
    );
    $this->assertEquals($keep_id, $run->id());
  }

  /**
   * @covers ::retrieveRunJson
   * @covers ::retrieveAllRunsJson
   */
  public function testRetrieveJson() {
    /** @var \Drupal\harvest\Entity\HarvestRunRepository $harvest_run_repo */
    $harvest_run_repo = $this->container->get('dkan.harvest.storage.harvest_run_repository');

    // There are no entities at this point, so trying to retrieve any should
    // result in NULL.
    $this->assertNull($harvest_run_repo->retrieveRunJson('plan', 'id'));

    // Set up an entity.
    $plan_id = 'plan';
    $run_id = '1711038293';
    $run_data = [
      'plan' => '{"plan": "json"}',
      'status' => [
        'extract' => 'AWESOME',
      ],
      'identifier' => $run_id,
    ];
    $harvest_run_repo->storeRun($run_data, $plan_id, $run_id);

    // Retrieve one run as JSON.
    $this->assertIsString(
      $run_json = $harvest_run_repo->retrieveRunJson($plan_id, $run_id)
    );
    $this->assertIsObject($run = json_decode($run_json));
    foreach (array_keys($run_data) as $key) {
      $this->assertObjectHasProperty($key, $run);
    }

    // Retrieve all runs as JSON.
    $this->assertIsArray(
      $runs_json = $harvest_run_repo->retrieveAllRunsJson($plan_id)
    );
    // Do some assertions.
    $this->assertArrayHasKey($run_id, $runs_json);
    $this->assertIsString($runs_json[$run_id]);
    $this->assertIsObject($run_decoded = json_decode($runs_json[$run_id]));
    $this->assertEquals($run_id, $run_decoded->identifier);
  }

  /**
   * @covers ::getExtractedUuids
   */
  public function testGetExtractedUuids() {
    /** @var \Drupal\harvest\Entity\HarvestRunRepository $harvest_run_repo */
    $harvest_run_repo = $this->container->get('dkan.harvest.storage.harvest_run_repository');
    $plan_id = 'plan';
    $run_id = '1711038293';

    // There are no entities at this point, so trying to get UUIDs should
    // result in an empty array.
    $this->assertIsArray($uuids = $harvest_run_repo->getExtractedUuids($plan_id, $run_id));
    $this->assertEquals([], $uuids);

    $uuids = [
      '4c774e90-7f9e-5d19-b168-ff9be1e69034',
      'c1eba32f-d2ec-48b2-b7c2-cc54bee22586',
      'f44522a8-66b8-406a-8bb2-78d796cde47c',
    ];
    // Set up an entity.
    $run_data = [
      'plan' => '{"plan": "json"}',
      'status' => [
        'extract' => 'AWESOME',
        'extracted_items_ids' => $uuids,
      ],
      'identifier' => $run_id,
    ];
    $harvest_run_repo->storeRun($run_data, $plan_id, $run_id);

    // Get the extracted UUIDs.
    $this->assertIsArray($extracted_uuids = $harvest_run_repo->getExtractedUuids($plan_id, $run_id));
    // Compare array_values() because $extracted_uuids will be keyed.
    $this->assertEquals($uuids, array_values($extracted_uuids));
  }

}
