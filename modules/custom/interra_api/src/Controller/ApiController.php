<?php

namespace Drupal\interra_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\dkan_api\Storage\DrupalNodeDataset;
use Drupal\dkan_datastore\Util;
use Drupal\dkan_schema\SchemaRetriever;
use JsonSchemaProvider\Provider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\dkan_schema\Schema;
use Drupal\interra_api\Interra;
use Drupal\interra_api\Search;
use Drupal\interra_api\SiteMap;
use Drupal\interra_api\Swagger;
use Drupal\interra_api\ApiRequest;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Dkan\Datastore\Manager\SimpleImport\SimpleImport;
use Dkan\Datastore\Resource;

/**
* An ample controller.
*/
class ApiController extends ControllerBase
{

  public function routes(Request $request)
  {
    $response = getRoutes();
    return new JsonResponse($response);
  }

  public function schemas(Request $request)
  {
    $response = array();
    $schema = new Schema();
    $interra = new Interra();
    $response['collections'] = $schema->config['collections'];
    $response['schema'] = $schema->loadFullSchema();
    $response['pageSchema'] = $interra->loadPageSchema();
    $response['facets'] = $schema->config['facets'];
    $response['map'] = ['organization' => ['name' => 'title']];
    return $this->response($response);
  }

  public function schema($schema_name)
  {
    $provider = new Provider(new SchemaRetriever());
    try {
      $schema = $provider->retrieve($schema_name);
    } catch (\Exception $e) {
      return $this->response($e->getMessage());
    }
    $response = $this->response(json_decode($schema));
    $response->headers->set("Content-Type", "application/schema+json");
    return $response;
  }

  public function search(Request $request)
  {
    $search = new Search();
    return $this->response($search->index());
  }

  public function response($resp)
  {
    $response = new JsonResponse($resp);
    $response->headers->set('Access-Control-Allow-Origin', '*');
    $response->headers->set('Access-Control-Allow-Methods', 'POST, GET, OPTIONS, PATCH, DELETE');
    $response->headers->set('Access-Control-Allow-Headers', 'Authorization');
    return $response;
  }

  public function collection($collection)
  {
    $valid_collections = [
      'dataset',
      'organization',
      'theme'
    ];

    $collection = str_replace(".json", "", $collection);

    if (in_array($collection, $valid_collections)) {

      /** @var DrupalNodeDataset $storage */
      $storage = \Drupal::service('dkan_api.storage.drupal_node_dataset');
      $data = $storage->retrieveAll();

      if ($collection == "dataset") {
        $json = "[" . implode(",", $data) . "]";
        $decoded = json_decode($json);

        foreach ($decoded as $key => $dataset) {
          $decoded[$key] = self::modifyDataset($dataset);
        }

        return $this->response($decoded);
      } elseif ($collection == "theme") {
        $themes = [];
        foreach ($data as $dataset_json) {
          $dataset = json_decode($dataset_json);

          if ($dataset->theme && is_array($dataset->theme)) {
            $theme = self::objectifyStringsArray($dataset->theme);
            $themes[$theme[0]->identifier] = $theme[0];
          }
        }

        ksort($themes);

        return $this->response(array_values($themes));
      } elseif ($collection == "organization") {
        $organizations = [];
        foreach ($data as $dataset_json) {
          $dataset = json_decode($dataset_json);

          if ($dataset->publisher) {
            $organizations[$dataset->publisher->name] = $dataset->publisher;
          }
        }

        ksort($organizations);

        return $this->response(array_values($organizations));
      }
    } else {
      throw new NotFoundHttpException();
    }
  }

  public function doc($collection, $doc)
  {
    $valid_collections = [
      'dataset'
    ];

    $uuid = str_replace(".json", "", $doc);

    if (in_array($collection, $valid_collections)) {

      if ($collection == "dataset") {

        /** @var DrupalNodeDataset $storage */
        $storage = \Drupal::service('dkan_api.storage.drupal_node_dataset');
        $data = $storage->retrieve($uuid);
        $dataset = json_decode($data);
        $dataset = $this->addDatastoreMetadata($dataset);
        return $this->response(self::modifyDataset($dataset));
      } else {
        return $this->response([]);
      }
    } else {
      throw new NotFoundHttpException();
    }
  }

  public static function modifyDataset($dataset)
  {
    foreach ($dataset->distribution as $key2 => $distro) {
      $format = str_replace("text/", "", $distro->mediaType);
      if ($format == "csv") {
        $distro->format = $format;
        $dataset->distribution[$key2] = $distro;
      } else {
        unset($dataset->distribution[$key2]);
      }
    }

    if ($dataset->theme && is_array($dataset->theme)) {
      $dataset->theme = self::objectifyStringsArray($dataset->theme);
    }

    if ($dataset->keyword && is_array($dataset->keyword)) {
      $dataset->keyword = self::objectifyStringsArray($dataset->keyword);
    }

    return $dataset;
  }

  public static function objectifyStringsArray(array $array)
  {
    $objects = [];
    foreach ($array as $string) {
      $identifier = str_replace(" ", "", $string);
      $identifier = strtolower($identifier);

      $objects[] = (object)['identifier' => $identifier, 'title' => $string];
    }

    return $objects;
  }

  private function addDatastoreMetadata($dataset) {
    $manager = Util::getDatastoreManager($dataset->identifier);

    if ($manager) {
      $headers = $manager->getTableHeaders();
      $dataset->columns = $headers;
      $dataset->datastore_statistics = [
        'rows' => $manager->numberOfRecordsImported(),
        'columns' => count($headers)
      ];
    }

    return $dataset;
  }

}

