<?php

namespace Drupal\Tests\metastore_search\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\metastore_search\Commands\RebuildTrackerCommands;
use Drupal\metastore_search\Controller\SearchController;
use Drush\TestTraits\DrushTestTrait;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\Request;

/**
 * @group dkan
 * @group metastore_search
 * @group functional
 * @group btb
 * @group functional2
 */
class SearchTest extends BrowserTestBase {

  use DrushTestTrait;

  protected static $modules = [
    'datastore',
    'metastore_search',
    'node',
  ];

  protected $defaultTheme = 'stark';

  public function testDrushCommands() {
    // Drush() defaults to expecting a 0 response code.
    $this->drush('dkan:metastore-search:rebuild-tracker');

    $command = new RebuildTrackerCommands();
    $command->rebuildTracker();
  }

  public function testControllers() {
    $base_uri = $this->container->get('request_stack')
      ->getCurrentRequest()
      ->getSchemeAndHttpHost();
    $client = new Client([
      'base_uri' => $base_uri,
      'timeout'  => 2.0,
      'http_errors' => FALSE,
    ]);

    $routes = [
      'api/1/search',
      'api/1/search/facets',
    ];

    foreach ($routes as $route) {
      $response = $client->get($route);
      $this->assertEquals('200', $response->getStatusCode());

      $response = $client->get($route, ['query' => ['page-size' => 'foo']]);
      $this->assertEquals('400', $response->getStatusCode());
    }

    $controller = SearchController::create($this->container);
    $request = Request::create($base_uri . '/api');

    $response = $controller->search($request);
    $this->assertEquals('200', $response->getStatusCode());

    $response = $controller->facets($request);
    $this->assertEquals('200', $response->getStatusCode());

    // Now test with errors.
    $request = Request::create(
      $base_uri . '/api',
      'GET',
      ['page-size' => 'foo']
    );
    $response = $controller->search($request);
    $this->assertEquals('400', $response->getStatusCode());

    $response = $controller->facets($request);
    $this->assertEquals('400', $response->getStatusCode());

    // Test past max page size
    $request = Request::create(
      $base_uri . '/api',
      'GET',
      ['page-size' => 200]
    );
    $response = $controller->search($request);
    $this->assertEquals('200', $response->getStatusCode());
    $this->assertEquals(
      '{"total":"0","results":[],"facets":[]}',
      $response->getContent()
    );
  }

}
