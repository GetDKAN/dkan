<?php

namespace Drupal\interra_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\interra_api\Schema;
use Drupal\interra_api\Search;
use Drupal\interra_api\Sitemap;

/**
* An ample controller.
*/
class ApiController extends ControllerBase {

  /**
  * Returns a render-able array for a test page.
  */
  public function routes( Request $request ) {
    $response = getRoutes();
    return new JsonResponse( $response );
  }

  /**
  * Returns a render-able array for a test page.
  */
  public function schema( Request $request ) {
    $response = array();
    $schema = new Schema('simple');
    $response['collections'] = $schema->config['collections'];
    $response['schema'] = $schema->loadFullSchema();
    $response['pageSchema'] = $schema->loadPageSchema();
    $response['facets'] = $schema->config['facets'];
    return new JsonResponse( $response );
  }

  /**
  * Returns a render-able array for a test page.
  */
  public function search( Request $request ) {
    $search = new Search();
    return new JsonResponse( $search->index() );
  }

  /**
  * Returns a render-able array for a test page.
  */
  public function siteMap( Request $request ) {
    $siteMap = new siteMap();
    return new JsonResponse( $siteMap->load() );
  }
}

