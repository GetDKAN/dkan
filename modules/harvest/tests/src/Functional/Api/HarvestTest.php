<?php

namespace Drupal\harvest\Tests\Functional\Api;

use Drupal\harvest\Load\Dataset;
use Drupal\Tests\BrowserTestBase;
use Drupal\user\Entity\User;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Harvest\ETL\Extract\DataJson;

/**
 * Test Harvest-related RESTful API.
 *
 * This test replaces Cypress tests:
 * - 02_harvest_empty.spec.js
 * - 03_harvest.spec.js
 *
 * @group dkan
 * @group harvest
 * @group functional
 * @group api
 *
 * @todo Add API schema validation to responses.
 * @todo Change the harvest so it doesn't involve a request across the
 *   internet.
 */
class HarvestTest extends BrowserTestBase {

  protected static $modules = [
    'harvest',
    'node',
    'user',
  ];

  protected $defaultTheme = 'stark';

  /**
   * Get a Guzzle Client object ready for sending HTTP requests.
   *
   * @param \Drupal\user\Entity\User|null $authUser
   *   (Optional) A user object to use for authentication.
   * @param bool $http_errors
   *   (Optional) Whether 4xx or 5xx response codes should throw an exception.
   *   Defaults to FALSE.
   *
   * @return \GuzzleHttp\Client
   *   Client ready for HTTP requests.
   *
   * @todo Move this to a trait or base class.
   */
  protected function getApiClient(?User $authUser = NULL, $http_errors = FALSE): Client {
    $options = [
      'base_uri' => $this->baseUrl,
      RequestOptions::HTTP_ERRORS => $http_errors,
    ];
    if ($authUser) {
      $options[RequestOptions::AUTH] = [$authUser->getAccountName(), $authUser->pass_raw];
    }
    return $this->container->get('http_client_factory')->fromOptions($options);
  }

  protected function addHarvestPlan($plan_identifier = 'test'): string {
    $harvest_plan = (object) [
      'identifier' => $plan_identifier,
      'extract' => (object) [
        'type' => DataJson::class,
        'uri' => 'https://dkan-default-content-files.s3.amazonaws.com/data.json',
      ],
      'load' => (object) [
        'type' => Dataset::class,
      ],
    ];
    /** @var \Drupal\harvest\HarvestService $harvest_service */
    $harvest_service = $this->container->get('dkan.harvest.service');
    $this->assertNotNull($storage_id = $harvest_service->registerHarvest($harvest_plan));
    return $storage_id;
  }

  public function testGetHarvestPlans() {
    $identifier = uniqid();
    $endpoint = '/api/1/harvest/plans';

    // Unauthenticated request to empty list of harvests, should yield 401.
    $response = $this->getApiClient()->get($endpoint);
    $this->assertEquals(401, $response->getStatusCode());

    // Authenticated request of empty list should yield 200.
    $user = $this->createUser(['harvest_api_index'], 'harvest_testapiuser', FALSE);
    $response = $this->getApiClient($user)->get($endpoint);
    $this->assertEquals(200, $response->getStatusCode());
    $this->assertEquals([], json_decode($response->getBody()->getContents()));

    // Add a harvest.
    $this->addHarvestPlan($identifier);

    // Unauthenticated request should yield 401.
    $response = $this->getApiClient()->get($endpoint);
    $this->assertEquals(401, $response->getStatusCode());

    // Authenticated request should yield 200.
    $response = $this->getApiClient($user)->get($endpoint);
    $this->assertEquals(200, $response->getStatusCode());
    // We see an identifier for a harvest which has not been run.
    $this->assertEquals([$identifier], json_decode($response->getBody()->getContents()));

    // Auth user can run this harvest.
    $post_user = $this->createUser(['harvest_api_run'], 'harvest_post_user', FALSE);
    $response = $this->getApiClient($post_user)->post('/api/1/harvest/runs', [
      RequestOptions::JSON => (object) ['plan_id' => (string) $identifier],
    ]);
    $this->assertEquals(200, $response->getStatusCode());
    $result = json_decode($response->getBody()->getContents(), TRUE);
    $this->assertEquals('SUCCESS', $result['result']['status']['extract'] ?? 'test fail');
  }

  public function testAnonymousPostHarvestPlans() {
    $endpoint = 'api/1/harvest/plans';
    $response = $this->getApiClient()->post($endpoint);
    $this->assertEquals(401, $response->getStatusCode());
  }

  public function testGetHarvestPlansPlanId() {
    $identifier = uniqid();
    $this->addHarvestPlan($identifier);
    $endpoint = '/api/1/harvest/plans/' . $identifier;

    $response = $this->getApiClient()->get($endpoint);
    $this->assertEquals(401, $response->getStatusCode());

    $user = $this->createUser(['harvest_api_index'], 'harvest_user', FALSE);
    $response = $this->getApiClient($user)->get($endpoint);
    $this->assertEquals(200, $response->getStatusCode());
    $result = json_decode($response->getBody()->getContents(), TRUE);
    $this->assertEquals($identifier, $result['identifier'] ?? 'test fail');
  }

  public function testGetHarvestRunsPlanIdQuery() {
    $identifier = uniqid();
    $this->addHarvestPlan($identifier);
    $query = ['plan' => $identifier];
    $endpoint = '/api/1/harvest/runs';

    /** @var \Drupal\harvest\HarvestService $harvest_service */
    $harvest_service = $this->container->get('dkan.harvest.service');
    $result = $harvest_service->runHarvest($identifier);
    $this->assertEquals('SUCCESS', $result['status']['extract'] ?? 'test fail');

    $response = $this->getApiClient()->get($endpoint, [
      RequestOptions::QUERY => $query,
    ]);
    $this->assertEquals(401, $response->getStatusCode());

    $user = $this->createUser(['harvest_api_info'], 'harvest_user', FALSE);
    $response = $this->getApiClient($user)->get($endpoint, [
      RequestOptions::QUERY => $query,
    ]);
    $this->assertEquals(200, $response->getStatusCode());
    $this->assertCount(1, json_decode($response->getBody()->getContents()));
  }

  public function testPostHarvestRuns() {
    $endpoint = '/api/1/harvest/runs';
    $response = $this->getApiClient()->post($endpoint);
    $this->assertEquals(401, $response->getStatusCode());
  }

  public function testGetHarvestRunsIdentifierQuery() {
    $identifier = uniqid();
    $endpoint = '/api/1/harvest/runs';

    // Add a harvest plan and run it.
    $this->addHarvestPlan($identifier);
    /** @var \Drupal\harvest\HarvestService $harvest_service */
    $harvest_service = $this->container->get('dkan.harvest.service');
    $result = $harvest_service->runHarvest($identifier);
    $this->assertEquals('SUCCESS', $result['status']['extract'] ?? 'test fail');

    // Unauthenticated user is 401.
    $response = $this->getApiClient()->get($endpoint);
    $this->assertEquals(401, $response->getStatusCode());

    // Authorized user with a plan available.
    $query = ['plan' => $identifier];
    $user = $this->createUser(['harvest_api_info'], 'test_user', FALSE);
    // Request the run ID.
    $response = $this->getApiClient($user)->get($endpoint, [
      RequestOptions::QUERY => $query,
    ]);
    // Request the run info.
    $result = json_decode($response->getBody()->getContents());
    $response = $this->getApiClient($user)->get($endpoint . '/' . $result[0] ?? 'bad_id', [
      RequestOptions::QUERY => $query,
    ]);
    $this->assertEquals(200, $response->getStatusCode());
    $result = json_decode($response->getBody()->getContents());
    $this->assertEquals('SUCCESS', $result->status->extract ?? 'test fail');
  }

}
