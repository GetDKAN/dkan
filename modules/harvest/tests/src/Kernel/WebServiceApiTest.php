<?php

namespace Drupal\Tests\harvest\Kernel;

use Drupal\harvest\HarvestService;
use Drupal\KernelTests\KernelTestBase;
use Drupal\harvest\WebServiceApi;
use Harvest\ETL\Extract\DataJson;
use Harvest\ETL\Load\Simple;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @covers \Drupal\harvest\WebServiceApi
 * @coversDefaultClass \Drupal\harvest\WebServiceApi
 *
 * @group dkan
 * @group harvest
 * @group kernel
 *
 * @see \Drupal\Tests\harvest\Unit\WebServiceApiTest
 */
class WebServiceApiTest extends KernelTestBase {

  protected static $modules = [
    'common',
    'harvest',
    'metastore',
    'node',
  ];

  protected function setUp() : void {
    parent::setUp();
    $this->installEntitySchema('harvest_plan');
    $this->installEntitySchema('harvest_hash');
    $this->installEntitySchema('harvest_run');
  }

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
   * Creates a mock HarvestService that will throw an exception on a method.
   *
   * @param $method
   *   The method to throw an exception.
   * @param $message
   *   (Optional) Message to include in the exception. Defaults to 'I am your
   *   error.'
   */
  protected function getExplodingHarvestService($method, $message = 'I am your error.') {
    $mock_harvest_service = $this->getMockBuilder(HarvestService::class)
      ->disableOriginalConstructor()
      ->onlyMethods([$method])
      ->getMock();
    $mock_harvest_service->method($method)
      ->willThrowException(new \Exception($message));
    return $mock_harvest_service;
  }

  protected function getPlanRequest(string $plan_identifier) {
    // Add plan to our request.
    return Request::create(
      'https://example.com',
      'GET',
      ['plan' => $plan_identifier]
    );
  }

  /**
   * @covers ::getPlan
   */
  public function testGetPlanErrors() {
    $plan_id = 'foo';
    $controller = WebServiceApi::create($this->container);
    $response = $controller->getPlan($plan_id);
    $this->assertEquals(404, $response->getStatusCode(), $response->getContent());
    $this->assertEquals('Unable to find plan ' . $plan_id, (json_decode($response->getContent()))->message);

    // Test exception handling.
    $message = 'harvest plan error';
    $this->container->set(
      'dkan.harvest.service',
      $this->getExplodingHarvestService('getHarvestPlanObject', $message)
    );

    $controller = WebServiceApi::create($this->container);
    $this->assertInstanceOf(Response::class, $response = $controller->getPlan($plan_id));
    $this->assertEquals(400, $response->getStatusCode());
    $this->assertIsObject($payload = json_decode($response->getContent()));
    $this->assertEquals($message, $payload->message);
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
  public function testDeregisterErrors() {
    $plan_id = 'foo';
    $controller = WebServiceApi::create($this->container);
    $response = $controller->deregister($plan_id);
    $this->assertEquals(404, $response->getStatusCode(), $response->getContent());
    $this->assertEquals('Unable to find plan ' . $plan_id, (json_decode($response->getContent()))->message);

    // Test exception handling.
    $message = 'the exception has this message which could contain sensitive SQL info.';
    $this->container->set(
      'dkan.harvest.service',
      $this->getExplodingHarvestService('deregisterHarvest', $message)
    );

    $controller = WebServiceApi::create($this->container);
    $this->assertInstanceOf(Response::class, $response = $controller->deregister($plan_id));
    $this->assertEquals(400, $response->getStatusCode());
    $this->assertIsObject($payload = json_decode($response->getContent()));
    // Response should not contain exception message.
    $this->assertStringNotContainsString($message, $payload->message);
    $this->assertEquals('Unable to deregister harvest plan ' . $plan_id, $payload->message);
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

    // Re-register the harvest and run it.
    $this->assertEquals($plan_identifier, $harvest_service->registerHarvest($plan));
    $run_status = $harvest_service->runHarvest($plan_identifier);
    $this->assertEquals('SUCCESS', $run_status['status']['extract'] ?? 'no success');

    // Deregister again with the same expectations.
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
  public function testInfoErrors() {
    $controller = WebServiceApi::create($this->container);
    $response = $controller->info();
    $this->assertEquals(400, $response->getStatusCode(), $response->getContent());
    $payload = json_decode($response->getContent(), TRUE);
    $this->assertEquals("Missing 'plan' query parameter value", $payload['message']);

    // Test exception handling.
    $plan_id = 'plan';
    $message = 'info error';
    $this->container->set(
      'dkan.harvest.service',
      $this->getExplodingHarvestService('getRunIdsForHarvest', $message)
    );
    $this->container->get('request_stack')->push($this->getPlanRequest($plan_id));

    $controller = WebServiceApi::create($this->container);
    $this->assertInstanceOf(Response::class, $response = $controller->info());
    $this->assertEquals(400, $response->getStatusCode());
    $this->assertIsObject($payload = json_decode($response->getContent()));
    $this->assertEquals($message, $payload->message);
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
    $request = $this->getPlanRequest($plan_identifier);
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
  public function testInfoRunErrors() {
    $controller = WebServiceApi::create($this->container);
    $response = $controller->infoRun('no_run');
    $this->assertEquals(400, $response->getStatusCode(), $response->getContent());
    $payload = json_decode($response->getContent(), TRUE);
    $this->assertEquals("Missing 'plan' query parameter value", $payload['message']);

    // Add non-existent plan to our request.
    $this->container->get('request_stack')->push($this->getPlanRequest('no_such_plan'));
    $response = $controller->infoRun('no_run');
    $this->assertEquals(200, $response->getStatusCode(), $response->getContent());
    $payload = json_decode($response->getContent());
    $this->assertIsObject($payload);
    $this->assertEmpty((array) $payload);

    // Test exception handling.
    $plan_id = 'plan';
    $message = 'info error';
    $this->container->set(
      'dkan.harvest.service',
      $this->getExplodingHarvestService('getHarvestRunInfo', $message)
    );

    $controller = WebServiceApi::create($this->container);
    $this->assertInstanceOf(Response::class, $response = $controller->infoRun($plan_id));
    $this->assertEquals(400, $response->getStatusCode());
    $this->assertIsObject($payload = json_decode($response->getContent()));
    $this->assertEquals($message, $payload->message);
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
    $request = $this->getPlanRequest($plan_identifier);
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
  public function testRevertErrors() {
    $controller = WebServiceApi::create($this->container);
    // Call revert() without supplying a plan as a request parameter.
    $response = $controller->revert();
    $this->assertEquals(400, $response->getStatusCode(), $response->getContent());
    $payload = json_decode($response->getContent(), TRUE);
    $this->assertEquals("Missing 'plan' query parameter value", $payload['message']);

    // Test exception handling.
    $plan_id = 'plan';
    $message = 'revert error';
    $this->container->set(
      'dkan.harvest.service',
      $this->getExplodingHarvestService('revertHarvest', $message)
    );
    $this->container->get('request_stack')->push($this->getPlanRequest('our_plan'));

    $controller = WebServiceApi::create($this->container);
    $this->assertInstanceOf(Response::class, $response = $controller->revert($plan_id));
    $this->assertEquals(400, $response->getStatusCode());
    $this->assertIsObject($payload = json_decode($response->getContent()));
    $this->assertEquals($message, $payload->message);
  }

  /**
   * @covers ::revert
   */
  public function testRevert() {
    // Register a harvest.
    /** @var \Drupal\harvest\HarvestService $harvest_service */
    $harvest_service = $this->container->get('dkan.harvest.service');
    $plan_identifier = 'test_plan';
    $plan = $this->getHarvestPlan($plan_identifier);
    $this->assertEquals($plan_identifier, $harvest_service->registerHarvest($plan));

    // Set up a request with our plan parameter so we can re-use it.
    $request = $this->getPlanRequest($plan_identifier);

    // Revert the plan before it's been run.
    $this->container->get('request_stack')->push($request);
    $controller = WebServiceApi::create($this->container);
    $this->assertInstanceOf(Response::class, $response = $controller->revert());
    $this->assertIsObject($payload = json_decode($response->getContent()));
    $this->assertEquals($plan_identifier, $payload->identifier);
    $this->assertEquals(0, $payload->result);

    // Run the plan.
    $run_status = $harvest_service->runHarvest($plan_identifier);
    $this->assertEquals('SUCCESS', $run_status['status']['extract'] ?? 'no success');

    // Revert the plan again.
    $this->container->get('request_stack')->push($request);
    $controller = WebServiceApi::create($this->container);
    $this->assertInstanceOf(Response::class, $response = $controller->revert());
    $this->assertIsObject($payload = json_decode($response->getContent()));
    $this->assertEquals($plan_identifier, $payload->identifier);
    $this->assertEquals(2, $payload->result);
  }

  /**
   * @covers ::run
   */
  public function testRunErrors() {
    $controller = WebServiceApi::create($this->container);
    $this->assertInstanceOf(Response::class, $response = $controller->run());
    $this->assertEquals(400, $response->getStatusCode());
    $this->assertIsObject($payload = json_decode($response->getContent()));
    $this->assertEquals('Invalid payload.', $payload->message);
    $this->assertEquals('/api/1/harvest', $payload->documentation);

    // Test exception handling.
    $plan_id = 'plan';
    $message = 'run error';
    $this->container->set(
      'dkan.harvest.service',
      $this->getExplodingHarvestService('runHarvest', $message)
    );
    $this->container->get('request_stack')->push(Request::create(
      'https://example.com',
      'POST', [], [], [], [],
      json_encode((object) ['plan_id' => $plan_id])
    ));

    $controller = WebServiceApi::create($this->container);
    $this->assertInstanceOf(Response::class, $response = $controller->run());
    $this->assertEquals(400, $response->getStatusCode());
    $this->assertIsObject($payload = json_decode($response->getContent()));
    $this->assertEquals($message, $payload->message);
  }

  /**
   * @covers ::index
   */
  public function testIndexException() {
    $message = 'no index here.';
    $this->container->set(
      'dkan.harvest.service',
      $this->getExplodingHarvestService('getAllHarvestIds', $message)
    );

    $controller = WebServiceApi::create($this->container);
    $this->assertInstanceOf(Response::class, $response = $controller->index());
    $this->assertIsObject($payload = json_decode($response->getContent()));
    $this->assertEquals($message, $payload->message);
  }

}
