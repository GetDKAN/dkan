<?php

namespace Drupal\Tests\metastore_search\Functional;

use Drupal\metastore_search\Commands\RebuildTrackerCommands;
use Drupal\metastore_search\Controller\SearchController;
use GuzzleHttp\Client;
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
    ]);

    $routes = [
      'api/1/search',
      'api/1/search/facets',
    ];

    foreach ($routes as $route) {
      $response = $client->get($route);
      $this->assertEquals('200', $response->getStatusCode());
    }

    $controller = SearchController::create(\Drupal::getContainer());

    $response = $controller->search([]);
    $this->assertEquals('200', $response->getStatusCode());

    $response = $controller->facets([]);
    $this->assertEquals('200', $response->getStatusCode());

  }

}
