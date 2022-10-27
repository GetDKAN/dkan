<?php

namespace Drupal\Tests\metastore_search\Functional;

use Drupal\metastore_search\Commands\RebuildTrackerCommands;
use Drupal\metastore_search\Controller\SearchController;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\Request;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Class SearchTest.
 *
 * @package Drupal\Tests\metastore_search\Functional
 * @group metastore_search
 */
class SearchTest extends ExistingSiteBase {

  /**
   *
   */
  public function testDrushCommands() {
    $output = NULL;
    $return = NULL;
    exec('drush dkan:metastore-search:rebuild-tracker', $output, $return);
    $this->assertEquals(0, $return);

    $command = new RebuildTrackerCommands();
    $command->rebuildTracker();
  }

  /**
   *
   */
  public function testControllers() {
    $client = new Client([
      'base_uri' => \Drupal::request()->getSchemeAndHttpHost(),
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

    $controller = SearchController::create(\Drupal::getContainer());
    $request = Request::create("http://blah/api");

    $response = $controller->search($request);
    $this->assertEquals('200', $response->getStatusCode());

    $response = $controller->facets($request);
    $this->assertEquals('200', $response->getStatusCode());

    // Now test with errors.
    $requestStack = \Drupal::service('request_stack');
    $params = ['page-size' => 'foo'];
    $request = $requestStack->pop()->duplicate($params);
    $requestStack->push($request);

    $response = $controller->search($request);
    $this->assertEquals('400', $response->getStatusCode());

    $response = $controller->facets($request);
    $this->assertEquals('400', $response->getStatusCode());

    // Test past max page size
    $requestStack = \Drupal::service('request_stack');
    $params = ['page-size' => 200];
    $request = $requestStack->pop()->duplicate($params);
    $requestStack->push($request);
    $response = $controller->search($request);
    // @todo Better assertion.
    $this->assertEquals('200', $response->getStatusCode());
  }

}
