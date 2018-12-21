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
use Drupal\interra_api\Swagger;
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
    $response['map'] = ['organization' => ['name' => 'title']];
    return $this->response($response );
  }

  public function search( Request $request ) {
    $search = new Search();
    return $this->response($search->index());
  }

  public function response ( $resp ) {
    $response = new JsonResponse( $resp );
    $response->headers->set('Access-Control-Allow-Origin', '*');
    $response->headers->set('Access-Control-Allow-Methods', 'POST, GET, OPTIONS, PATCH, DELETE');
    $response->headers->set('Access-Control-Allow-Headers', 'Authorization');
    return $response;
  }

  public function siteMap( Request $request ) {
    $siteMap = new SiteMap();
    return $this->response($siteMap->load());
  }

  public function swagger( Request $request ) {
    $swagger = new Swagger();
    return $this->response($swagger->load());
  }

  public function collection( Request $request ) {
    $apiRequest = new ApiRequest();
    $uri = $request->getPathInfo();
    $path = $apiRequest->getUri($uri);
    if ($collection = $apiRequest->validateCollectionPath($path)) {
      $load = new Load();
      $docs = $load->loadByType($collection);
      return $this->response($docs);
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
      $collection = explode('/', $path)[1];
      $schema = new Schema();
      $entity = $schema->config['collectionToEntityMap'][$collection];
      $load = new Load();
      if ($doc = $load->loadAPIDoc($id, $entity)) {
        return $this->response($doc);
      }
      throw new NotFoundHttpException();
    }
    throw new NotFoundHttpException();
  }

  public function subdoc( Request $request ) {
    $apiRequest = new ApiRequest();
    $uri = $request->getPathInfo();
    $path = $apiRequest->getUri($uri);
    if ($id = $apiRequest->validateDocPath($path)) {
      $collection = explode('/', $path)[1];
      $schema = new Schema();
      $entity = $schema->config['collectionToEntityMap'][$collection];
      $load = new Load();
      if ($doc = $load->loadDocById($id, $entity)) {
        $formatted = $load->formatDoc($doc);
        $dereferenced = $load->dereference($formatted);
        return $this->response($dereferenced);
      }
      throw new NotFoundHttpException();
    }
    throw new NotFoundHttpException();
  }
}

