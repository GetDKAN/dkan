<?php

namespace Drupal\harvest\Tests\Functional\Api;

use Drupal\Tests\BrowserTestBase;
use Drupal\user\Entity\User;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\RequestOptions;

/**
 * This test replaces Cypress test 02_harvest_empty.spec.js.
 *
 * @group dkan
 * @group harvest
 * @group functional
 * @group api
 *
 * @todo Combine this with other Harvest REST API tests for efficiency.
 */
class HarvestEmptyTest extends BrowserTestBase {

  protected static $modules = [
    'harvest',
    'node',
    'user',
  ];

  protected $defaultTheme = 'stark';

  /**
   * Get a Guzzle Client object ready for sending requests.
   *
   * @param \Drupal\user\Entity\User|null $authUser
   *   (Optional) A user object to use for authentication.
   * @param bool $http_errors
   *   (Optional) Whether 4xx or 5xx response codes should throw an exception.
   *   Defaults to FALSE.
   *
   * @return \GuzzleHttp\ClientInterface
   *   Client ready for HTTP requests.
   */
  protected function getApiClient(?User $authUser = NULL, $http_errors = FALSE): ClientInterface {
    $options = [
      'base_uri' => $this->baseUrl,
      RequestOptions::HTTP_ERRORS => $http_errors,
    ];
    if ($authUser) {
      $options[RequestOptions::AUTH] = [$authUser->getAccountName(), $authUser->pass_raw];
    }
    return $this->container->get('http_client_factory')->fromOptions($options);
  }

  public function testEmptyHarvestList() {
    $endpoint = '/api/1/harvest/plans';
    // Unauthenticated request to empty list of harvests, should yield 401.
    $response = $this->getApiClient()->get($endpoint);
    $this->assertEquals(401, $response->getStatusCode());

    // Authenticated request of empty list should yield 200.
    $user = $this->createUser(['harvest_api_index'], 'harvest_testapiuser', FALSE);
    $response = $this->getApiClient($user)->get($endpoint);
    $this->assertEquals(200, $response->getStatusCode());
    $this->assertEquals([], json_decode($response->getBody()->getContents()));
  }

}
