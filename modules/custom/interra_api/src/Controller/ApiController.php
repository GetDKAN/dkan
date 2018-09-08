<?php

namespace Drupal\interra_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\dkan_schema\Schema;
use Drupal\interra_api\Interra;
use Drupal\interra_api\Search;
use Drupal\interra_api\Load;
use Drupal\interra_api\SiteMap;
use Drupal\interra_api\ApiRequest;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
* An ample controller.
*/
class ApiController extends ControllerBase {

  public function routes( Request $request ) {
    $response = getRoutes();
    return new JsonResponse( $response );
  }

  public function schema( Request $request ) {
    $response = array();
    $schema = new Schema();
    $interra = new Interra();
    $response['collections'] = $schema->config['collections'];
    $response['schema'] = $schema->loadFullSchema();
    $response['pageSchema'] = $interra->loadPageSchema();
    $response['facets'] = $schema->config['facets'];
    return new JsonResponse( $response );
  }

  public function search( Request $request ) {
    $search = new Search();
    return new JsonResponse( $search->index() );
  }

  public function siteMap( Request $request ) {
    $siteMap = new SiteMap();
    return new JsonResponse( $siteMap->load() );
  }

  public function collection( Request $request ) {
    $apiRequest = new ApiRequest();
    $uri = $request->getPathInfo();
    $path = $apiRequest->getUri($uri);
    dpm($path);
    if ($collection = $apiRequest->validateCollectionPath($path)) {
      $load = new Load();
      $docs = $load->loadByType($collection);
      return new JsonResponse( $docs );
    }
    else {
      throw new NotFoundHttpException();
    }
  }

  public function doc( Request $request ) {
    $apiRequest = new ApiRequest();
    $uri = $request->getPathInfo();
    $path = $apiRequest->getUri($uri);
    if ($id = $apiRequest->validateDocPath($path)) {
      $load = new Load();
      if ($doc = $load->loadDocById($id)) {
        return new JsonResponse( $load->formatDoc($doc) );
      }
      throw new NotFoundHttpException();
    }
    throw new NotFoundHttpException();
  }
}

