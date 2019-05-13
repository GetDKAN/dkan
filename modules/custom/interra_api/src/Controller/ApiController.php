<?php

namespace Drupal\interra_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\dkan_datastore\Util;
use JsonSchemaProvider\Provider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\dkan_schema\Schema;
use Drupal\interra_api\Search;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * An ample controller.
 */
class ApiController extends ControllerBase {

  /**
   *
   */
  public function schemas(Request $request) {
    try {
      $schema = $this->fetchSchema('dataset');
    }
    catch (\Exception $e) {
      return $this->response($e->getMessage());
    }

    $data = ['dataset' => json_decode($schema)];
    return $this->jsonResponse($data);

  }

  /**
   *
   */
  public function schema($schema_name) {
    try {
      $schema = $this->fetchSchema($schema_name);
    }
    catch (\Exception $e) {
      return $this->response($e->getMessage());
    }

    return $this->jsonResponse(json_decode($schema));

  }

  /**
   *
   * @param string $schema_name
   * @return string Schema
   */
  protected function fetchSchema($schema_name) {
    $provider = $this->getSchemaProvider();
    return $provider->retrieve($schema_name);
  }

  /**
   *
   */
  public function search(Request $request) {
    /** @var \Drupal\interra_api\Search $search */
    $search = \Drupal::service('interra_api.search');
    return $this->response($search->index());
  }

  /**
   *
   * @TODO very high CRAP score. consider refactoring. use routing to split to different method?
   * @param mixed $collection
   * @return mixed
   * @throws NotFoundHttpException
   */
  public function collection($collection) {
    $valid_collections = [
      'dataset',
      'organization',
      'theme',
    ];

    $collection = str_replace(".json", "", $collection);
    /** @var \Drupal\interra_api\Service\DatasetModifier $datasetModifier */
    $datasetModifier = \Drupal::service('interra_api.service.dataset_modifier');

    if (in_array($collection, $valid_collections)) {

      /** @var \Drupal\dkan_api\Storage\DrupalNodeDataset $storage */
      $storage = \Drupal::service('dkan_api.storage.drupal_node_dataset');
      $data = $storage->retrieveAll();

      if ($collection == "dataset") {
        $json = "[" . implode(",", $data) . "]";
        $decoded = json_decode($json);

        foreach ($decoded as $key => $dataset) {
          $decoded[$key] = $datasetModifier->modifyDataset($dataset);
        }

        return $this->response($decoded);
      }
      elseif ($collection == "theme") {
        $themes = [];
        foreach ($data as $dataset_json) {
          $dataset = json_decode($dataset_json);

          if ($dataset->theme && is_array($dataset->theme)) {
            $theme = $datasetModifier->objectifyStringsArray($dataset->theme);
            $themes[$theme[0]->identifier] = $theme[0];
          }
        }

        ksort($themes);

        return $this->response(array_values($themes));
      }
      elseif ($collection == "organization") {
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
    }
    else {
      throw new NotFoundHttpException();
    }
  }

  /**
   *
   *
   * @param mixed $collection
   * @param mixed $doc
   * @return mixed
   */
  public function doc($collection, $doc) {
    // Array of.
    $valid_collections = [
      'dataset' => [$this, 'docDatasetHandler'],
    ];

    if (
            isset($valid_collections[$collection])
            && is_callable($valid_collections[$collection])
    ) {

      // @TODO this is refactor to reduce CRAP score.
      //       Not sure if additional params need to be passed
      return $this->response(call_user_func($valid_collections[$collection], $doc));
    }
    else {
      return $this->response([]);
    }
  }

  /**
   * Handles dataset collections for doc.
   *
   * @param string $doc
   *
   * @return mixed
   */
  protected function docDatasetHandler($doc) {
    $uuid = str_replace(".json", "", $doc);
    /** @var \Drupal\dkan_api\Storage\DrupalNodeDataset $storage */
    $storage = \Drupal::service('dkan_api.storage.drupal_node_dataset');
    /** @var \Drupal\interra_api\Service\DatasetModifier $datasetModifuer */
    $datasetModifier = \Drupal::service('interra_api.service.dataset_modifier');
    $data            = $storage->retrieve($uuid);
    $dataset         = json_decode($data);
    $dataset         = $this->addDatastoreMetadata($dataset);
    return $datasetModifier->modifyDataset($dataset);
  }

  /**
   *
   * @param \stdClass $dataset
   * @return \stdClass Same
   */
  protected function addDatastoreMetadata(\stdClass $dataset) {
    $manager = $this->getDatastoreManager($dataset->identifier);

    if ($manager) {
      try {
        $headers = $manager->getTableHeaders();
        $dataset->columns = $headers;
        $dataset->datastore_statistics = [
          'rows' => $manager->numberOfRecordsImported(),
          'columns' => count($headers),
        ];
      }
      catch (\Exception $e) {
        // @todo log this?
      }
    }

    return $dataset;
  }

  /**
   *
   * @param mixed $resp
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  protected function response($resp) {
    /** @var \Symfony\Component\HttpFoundation\JsonResponse $response */
    $response = \Drupal::service('dkan.factory')
      ->newJsonResponse($resp);
    $response->headers->set('Access-Control-Allow-Origin', '*');
    $response->headers->set('Access-Control-Allow-Methods', 'POST, GET, OPTIONS, PATCH, DELETE');
    $response->headers->set('Access-Control-Allow-Headers', 'Authorization');
    return $response;
  }

  /**
   *
   * @param mixed $resp
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  protected function jsonResponse($resp) {
    $response = $this->response($resp);
    // @todo is this necessary? it's already a JsonResponse object
    $response->headers->set("Content-Type", "application/schema+json");
    return $response;
  }

  /**
   * New instance of Schema provider.
   *
   * @codeCoverageIgnore
   *
   * @return \JsonSchemaProvider\Provider
   *   Provider instance.
   */
  protected function getSchemaProvider() {
    $schmaRetriever = \Drupal::service('dkan_schema.schema_retriever');
    return new Provider($schmaRetriever);
  }

  /**
   * @todo refactor to not use static call
   * @param string $uuid
   * @return \Dkan\Datastore\Manager\IManager
   */
  protected function getDatastoreManager($uuid) {
    return Util::getDatastoreManager($uuid);
  }

}
