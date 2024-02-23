<?php

namespace Drupal\Tests\harvest\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\harvest\WebServiceApi;
use Harvest\ETL\Extract\DataJson;
use Harvest\ETL\Load\Simple;
use Symfony\Component\HttpFoundation\Request;

/**
 * @covers \Drupal\harvest\WebServiceApi
 * @coversDefaultClass \Drupal\harvest\WebServiceApi
 *
 * @group dkan
 * @group harvest
 * @group kernel
 *
 * @todo Add mocks that throw exceptions so we can test code paths to the
 *   exception-handling part of the controller.
 */
class WebServiceApiTest extends KernelTestBase {

  protected static $modules = [
    'common',
    'harvest',
    'metastore',
    'node',
  ];

  protected function getHarvestPlan(string $plan_identifier): object {
    return (object) [
      'identifier' => $plan_identifier,
      'extract' => (object) [
        'type' => DataJson::class,
        'uri' => 'file://' . realpath(__DIR__ . '/../../files/data.json'),
      ],
      'transforms' => [],
      'load' => (object) [
        'type' => Simple::class,
      ],
    ];
  }

  /**
   * @covers ::getPlan
   */
  public function testGetPlanNoPlan() {
    $this->markTestIncomplete('getPlan() tries to json encode null if the record is not found.');
    $controller = WebServiceApi::create($this->container);
    $response = $controller->getPlan('foo');
    $this->assertEquals(404, $response->getStatusCode(), $response->getContent());
  }

  /**
   * @covers ::getPlan
   */
  public function testGetPlan() {
    // Register a harvest.
    /** @var \Drupal\harvest\HarvestService $harvest_service */
    $harvest_service = $this->container->get('dkan.harvest.service');
    $plan_identifier = 'test_plan';
    $plan = $this->getHarvestPlan($plan_identifier);
    $this->assertEquals($plan_identifier, $harvest_service->registerHarvest($plan));

    // Call getPlan() on an un-run harvest.
    $controller = WebServiceApi::create($this->container);
    $response = $controller->getPlan($plan_identifier);
    $this->assertEquals(200, $response->getStatusCode(), $response->getContent());
    $this->assertEquals(json_encode($plan), $response->getContent());

    // Run the harvest.
    $result = $harvest_service->runHarvest($plan_identifier);
    $this->assertEquals('SUCCESS', $result['status']['extract'] ?? 'not success');
    $this->assertArrayNotHasKey('errors', $result);

    // Get the plan again. Same result.
    $response = $controller->getPlan($plan_identifier);
    $this->assertEquals(200, $response->getStatusCode(), $response->getContent());
    $this->assertEquals(json_encode($plan), $response->getContent());
  }

  /**
   * @covers ::deregister
   */
  public function testDeregisterNoPlan() {
    $this->markTestIncomplete('Returns 400 plus SQL errors. Should return 404(?) and not show SQL errors.');
    $controller = WebServiceApi::create($this->container);
    $response = $controller->deregister('foo');
    $this->assertEquals(404, $response->getStatusCode(), $response->getContent());
  }

  public function testDeregister() {
    // Register a harvest.
    /** @var \Drupal\harvest\HarvestService $harvest_service */
    $harvest_service = $this->container->get('dkan.harvest.service');
    $plan_identifier = 'test_plan';
    $plan = $this->getHarvestPlan($plan_identifier);
    $this->assertEquals($plan_identifier, $harvest_service->registerHarvest($plan));

    // Deregister the harvest.
    $controller = WebServiceApi::create($this->container);
    $response = $controller->deregister($plan_identifier);
    $this->assertEquals(200, $response->getStatusCode(), $response->getContent());
    $this->assertEquals(
      json_encode((object) ['identifier' => $plan_identifier]),
      $response->getContent()
    );
  }

  /**
   * @covers ::info
   */
  public function testInfoNoPlan() {
    $controller = WebServiceApi::create($this->container);
    $response = $controller->info();
    $this->assertEquals(400, $response->getStatusCode(), $response->getContent());
    $payload = json_decode($response->getContent(), TRUE);
    $this->assertEquals("Missing 'plan' query parameter value", $payload['message']);
  }

  /**
   * @covers ::info
   */
  public function testInfo() {
    // Register a harvest.
    /** @var \Drupal\harvest\HarvestService $harvest_service */
    $harvest_service = $this->container->get('dkan.harvest.service');
    $plan_identifier = 'test_plan';
    $plan = $this->getHarvestPlan($plan_identifier);
    $this->assertEquals($plan_identifier, $harvest_service->registerHarvest($plan));

    // Add plan to our request.
    // @todo Modify the controller so that we pass in a Request object to the
    //   method instead of using the stack.
    $request = Request::create(
      'https://example.com',
      'GET',
      ['plan' => $plan_identifier]
    );
    $this->container->get('request_stack')->push($request);

    // Get the info before running. This should result in an empty list.
    $controller = WebServiceApi::create($this->container);
    $response = $controller->info();
    $this->assertEquals(200, $response->getStatusCode(), $response->getContent());
    $this->assertCount(0, json_decode($response->getContent()));

    // Run the harvest.
    $result = $harvest_service->runHarvest($plan_identifier);
    $this->assertEquals('SUCCESS', $result['status']['extract'] ?? 'not success');
    $this->assertArrayNotHasKey('errors', $result);

    // Get info again, now with results.
    $this->container->get('request_stack')->push($request);
    $response = $controller->info();
    $this->assertEquals(200, $response->getStatusCode(), $response->getContent());
    $this->assertCount(1, json_decode($response->getContent()));
  }

  /**
   * @covers ::infoRun
   */
  public function testInfoRunNoPlanNoRun() {
    $controller = WebServiceApi::create($this->container);
    $response = $controller->infoRun('no_run');
    $this->assertEquals(400, $response->getStatusCode(), $response->getContent());
    $payload = json_decode($response->getContent(), TRUE);
    $this->assertEquals("Missing 'plan' query parameter value", $payload['message']);

    // Add non-existent plan to our request.
    $this->container->get('request_stack')->push(Request::create(
      'https://example.com',
      'GET',
      ['plan' => 'no_such_plan']
    ));
    $response = $controller->infoRun('no_run');
    $this->assertEquals(200, $response->getStatusCode(), $response->getContent());
    $payload = json_decode($response->getContent());
    $this->assertIsObject($payload);
    $this->assertEmpty((array) $payload);
  }

  /**
   * @covers ::infoRun
   */
  public function testInfoRun() {
    // Register a harvest.
    /** @var \Drupal\harvest\HarvestService $harvest_service */
    $harvest_service = $this->container->get('dkan.harvest.service');
    $plan_identifier = 'test_plan';
    $plan = $this->getHarvestPlan($plan_identifier);
    $this->assertEquals($plan_identifier, $harvest_service->registerHarvest($plan));

    // Add plan to our request.
    $request = Request::create(
      'https://example.com',
      'GET',
      ['plan' => $plan_identifier]
    );
    $this->container->get('request_stack')->push($request);

    // Plan exists but run ID does not.
    $controller = WebServiceApi::create($this->container);
    $response = $controller->infoRun('no_run');
    $this->assertEquals(200, $response->getStatusCode(), $response->getContent());
    $payload = json_decode($response->getContent());
    $this->assertIsObject($payload);
    $this->assertEmpty((array) $payload);

    // Run the harvest.
    $result = $harvest_service->runHarvest($plan_identifier);
    $this->assertEquals('SUCCESS', $result['status']['extract'] ?? 'not success');
    $this->assertArrayNotHasKey('errors', $result);

    // Get the run ID. Method runHarvest does not return this information.
    $this->container->get('request_stack')->push($request);
    $run_ids = json_decode($controller->info()->getContent());

    // Call infoRun() with both plan and run IDs.
    $this->container->get('request_stack')->push($request);
    $response = $controller->infoRun(reset($run_ids));
    $this->assertEquals(200, $response->getStatusCode(), $response->getContent());
    // Response is an object.
    $this->assertIsObject(json_decode($response->getContent()));
    // Decode as an array for convenience.
    $this->assertNotEmpty($payload = json_decode($response->getContent(), TRUE));
    $this->assertEquals('SUCCESS', $payload['status']['extract'] ?? 'no success');
  }

  /**
   * @covers ::revert
   */
  public function testRevertNoPlan() {
    $controller = WebServiceApi::create($this->container);
    $response = $controller->revert('foo');
    $this->assertEquals(400, $response->getStatusCode(), $response->getContent());
    $payload = json_decode($response->getContent(), TRUE);
    $this->assertEquals("Missing 'plan' query parameter value", $payload['message']);
  }

  public function testRevert() {
    $this->markTestIncomplete('Add a test here.');
  }

}
