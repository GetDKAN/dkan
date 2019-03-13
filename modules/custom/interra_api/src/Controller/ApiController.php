<?php

namespace Drupal\interra_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\dkan_schema\SchemaRetriever;
use JsonSchemaProvider\Provider;
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
use Dkan\Datastore\Manager\SimpleImport\SimpleImport;
use Dkan\Datastore\Resource;

/**
* An ample controller.
*/
class ApiController extends ControllerBase {

  public function routes( Request $request ) {
    $response = getRoutes();
    return new JsonResponse( $response );
  }

  public function schemas( Request $request ) {
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

  public function schema($schema_name) {
      $provider = new Provider(new SchemaRetriever());
      try {
          $schema = $provider->retrieve($schema_name);
      }
      catch (\Exception $e) {
          return $this->response($e->getMessage());
      }
      $response = $this->response(json_decode($schema));
      $response->headers->set("Content-Type", "application/schema+json");
      return $response;
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

        if ($collection == "dataset") {
          $node = $load->loadDocById($id);
          $database = \Drupal::service('dkan_datastore.database');
          $resource = new Resource($node->id(), $doc->distribution[0]->downloadURL);
          $provider = new \Dkan\Datastore\Manager\InfoProvider();
          $provider->addInfo(new \Dkan\Datastore\Manager\Info(SimpleImport::class, "simple_import", "SimpleImport"));
          $bin_storage = new \Dkan\Datastore\LockableBinStorage("dkan_datastore", new \Dkan\Datastore\Locker("dkan_datastore"), new \Drupal\dkan_datastore\Storage\Variable());
          $factory = new \Dkan\Datastore\Manager\Factory($resource, $provider, $bin_storage, $database);
          /* @var $datastore \Dkan\Datastore\Manager\SimpleImport\SimpleImport */
          $datastore = $factory->get();

          if ($datastore) {
            $headers = $datastore->getTableHeaders();
            $doc->columns = $headers;
            $doc->datastore_statistics = [
              'rows' => $datastore->numberOfRecordsImported(),
              'columns' => count($headers)
            ];
          }
        }

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

