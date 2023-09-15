<?php

namespace Drupal\metastore;

use Drupal\common\DkanApiDocsGenerator;
use Drupal\common\Util\ApiDocsPathModifier;
use Drupal\Core\Site\Settings;

/**
 * Provides dataset-specific OpenAPI documentation.
 */
class DatasetApiDocs {

  const SPEC_PARAMETERS = [
    'datasetUuid',
    'showReferenceIds',
    'datastoreDistributionUuid',
    'datastoreQueryProperties',
    'datastoreQueryConditions',
    'datastoreQueryLimit',
    'datastoreQueryOffset',
    'datastoreQuerySorts',
    'datastoreQueryCount',
    'datastoreQueryResults',
    'datastoreQuerySchema',
    'datastoreQueryKeys',
    'datastoreQueryFormat',
    'datastoreQueryRowIds',
    'datastoreDatasetUuid',
    'datastoreDistributionIndex',
  ];

  const SPEC_SCHEMAS = [
    'dataset',
    'errorResponse',
    'datastoreResourceQuery',
    'datastoreQueryResource',
    'datastoreQueryProperty',
    'datastoreQueryExpression',
    'datastoreQueryCondition',
    'datastoreQueryConditionGroup',
    'datastoreQuerySort',
    'datastoreQueryResourceProperty',
    'datastoreQuery',

  ];

  const SPEC_RESPONSES = [
    '404IdNotFound',
    '200JsonOrCsvQueryOk',
    '400BadJson',
  ];

  /**
   * OpenAPI spec for dataset-related endpoints.
   *
   * @var \Drupal\common\DkanApiDocsGenerator
   */
  private $docsGenerator;

  /**
   * OpenAPI spec for dataset-related endpoints.
   *
   * @var \Drupal\metastore\MetastoreService
   */
  private $metastore;

  /**
   * Prefix to the API path.
   *
   * @var string
   */
  private string $dkanApiBase;

  /**
   * Constructs a new MetastoreDocsController.
   *
   * @param \Drupal\common\DkanApiDocsGenerator $docsGenerator
   *   Serves openapi spec.
   * @param \Drupal\metastore\MetastoreService $metastore
   *   The metastore service.
   * @param \Drupal\Core\Site\Settings $settings
   *   The Drupal settings service.
   */
  public function __construct(DkanApiDocsGenerator $docsGenerator, MetastoreService $metastore, Settings $settings) {
    $this->docsGenerator = $docsGenerator;
    $this->metastore = $metastore;
    $this->dkanApiBase = $settings->get('dkan_api_base') ?? '';
  }

  /**
   * Returns only dataset-specific GET requests for the API spec.
   *
   * @param string $identifier
   *   Dataset uuid.
   *
   * @return array
   *   OpenAPI spec.
   */
  public function getDatasetSpecific(string $identifier) {
    $specs = ['metastore_api_docs', 'datastore_api_docs'];
    $fullSpec = $this->docsGenerator->buildSpec($specs)->{"$"};

    $datasetSpec = [
      'openapi' => $fullSpec['openapi'],
      'info' => $fullSpec['info'],
    ];

    $metastorePath = $fullSpec['paths'][$this->dkanApiBase . '/api/1/metastore/schemas/dataset/items/{identifier}']['get'];
    unset($metastorePath['parameters'][0]);
    $metastorePath['parameters'] = array_values($metastorePath['parameters']);
    $datasetSpec['paths']["/api/1/metastore/schemas/dataset/items/$identifier"]['get'] = $metastorePath;

    $datasetSpec['paths']["/api/1/datastore/query/$identifier/{index}"]
      = $this->getDatastoreIndexPath($fullSpec, $identifier);

    $datasetSpec['paths']['/api/1/datastore/query/{distributionId}'] =
      $fullSpec['paths'][$this->dkanApiBase . '/api/1/datastore/query/{distributionId}'];

    $datasetSpec['paths']['/api/1/datastore/sql'] =
      $fullSpec['paths'][$this->dkanApiBase . '/api/1/datastore/sql'];

    $datasetSpec['components'] = $this->datasetSpecificComponents($fullSpec, $identifier);

    $this->alterDatastoreParameters($datasetSpec, $identifier);
    $this->modifySqlEndpoints($datasetSpec, $identifier);
    if ($this->dkanApiBase) {
      $datasetSpec = ApiDocsPathModifier::prepend($datasetSpec, $this->dkanApiBase);
    }

    return $datasetSpec;
  }

  /**
   * Set up components object for dataset-specific docs.
   *
   * @param mixed $fullSpec
   *   The full docs spec.
   * @param mixed $identifier
   *   Dataset identifier.
   *
   * @return array
   *   Components object (associative array).
   */
  private function datasetSpecificComponents($fullSpec, $identifier) {
    $components = [];
    $components['parameters'] =
      $this->datasetSpecificParameters($fullSpec['components']['parameters'], $identifier);
    $components['schemas'] =
      $this->datasetSpecificSchemas($fullSpec['components']['schemas']);
    $components['responses'] =
      $this->datasetSpecificResponses($fullSpec['components']['responses']);

    return $components;
  }

  /**
   * Redo the datastore index endpoint assuming the ID is included in the path.
   *
   * @param mixed $fullSpec
   *   Full site spec.
   * @param mixed $identifier
   *   Dataset identifier.
   *
   * @return array
   *   Path array ready to insert.
   */
  private function getDatastoreIndexPath($fullSpec, $identifier) {
    $datastoreIndexPath = $fullSpec['paths'][$this->dkanApiBase . '/api/1/datastore/query/{datasetId}/{index}'];
    unset($datastoreIndexPath['get']['parameters'][0]);
    $datastoreIndexPath['get']['parameters'] = array_values($datastoreIndexPath['get']['parameters']);
    unset($datastoreIndexPath['post']['parameters'][0]);
    $datastoreIndexPath['post']['parameters'] = array_values($datastoreIndexPath['post']['parameters']);
    return $datastoreIndexPath;
  }

  /**
   * Get just the schemas we need.
   *
   * @param array $schemas
   *   Schemas array from spec.
   *
   * @return array
   *   Filtered array.
   */
  private function datasetSpecificSchemas(array $schemas) {
    $newSchemas = array_filter($schemas, function ($key) {
      if (in_array($key, self::SPEC_SCHEMAS)) {
        return TRUE;
      }
      return FALSE;
    }, ARRAY_FILTER_USE_KEY);
    return $newSchemas;
  }

  /**
   * Just the parameters we need.
   *
   * @param array $parameters
   *   Parameters array.
   * @param mixed $identifier
   *   Dataset identifier.
   *
   * @return array
   *   Filtered parameters.
   */
  private function datasetSpecificParameters(array $parameters, $identifier) {
    $newParameters = array_filter($parameters, function ($key) {
      if (in_array($key, self::SPEC_PARAMETERS)) {
        return TRUE;
      }
      return FALSE;
    }, ARRAY_FILTER_USE_KEY);
    $newParameters['datasetUuid']['example'] = $identifier;
    return $newParameters;
  }

  /**
   * Get just the responses we need.
   *
   * @param array $responses
   *   Schemas array from spec.
   *
   * @return array
   *   Filtered array.
   */
  private function datasetSpecificResponses(array $responses) {
    $newResponses = array_filter($responses, function ($key) {
      if (in_array($key, self::SPEC_RESPONSES)) {
        return TRUE;
      }
      return FALSE;
    }, ARRAY_FILTER_USE_KEY);
    return $newResponses;
  }

  /**
   * Alter the generic sql endpoint to be specific to the current dataset.
   *
   * @param array $spec
   *   The paths defined in the original spec.
   * @param string $identifier
   *   Dataset uuid.
   */
  private function alterDatastoreParameters(array &$spec, string $identifier) {
    $spec['components']['parameters']['datastoreDatasetUuid']['example'] = $identifier;
    foreach ($this->getDistributions($identifier) as $index => $dist) {
      unset($spec['components']['parameters']['datastoreDistributionUuid']['example']);
      $spec['components']['parameters']['datastoreDistributionUuid']['examples'][$dist['identifier']] = [
        'value' => $dist['identifier'],
        'summary' => $dist["data"]["title"] ?? $dist['identifier'],
      ];
      unset($spec['components']['parameters']['datastoreDistributionIndex']['example']);
      $spec['components']['parameters']['datastoreDistributionIndex']['examples']["index{$index}"] = [
        'value' => "$index",
        'summary' => $dist["data"]["title"] ?? $dist['identifier'],
      ];
    }
  }

  /**
   * Modify the generic sql endpoint to be specific to the current dataset.
   *
   * @param array $spec
   *   The original spec.
   * @param string $identifier
   *   Dataset uuid.
   */
  private function modifySqlEndpoints(array &$spec, string $identifier) {

    foreach ($this->getSqlPathsAndOperations($spec['paths']) as $path => $operations) {
      foreach ($this->getDistributions($identifier) as $dist) {
        $newOperations = $this->modifySqlEndpoint($operations, $dist);
        $spec['paths'][$path] = $newOperations;
      }
    }
  }

  /**
   * Arrange paths for SQL endpoint.
   */
  private function getSqlPathsAndOperations($pathsAndOperations) {
    foreach (array_keys($pathsAndOperations) as $path) {
      if (substr_count($path, 'sql') == 0) {
        unset($pathsAndOperations[$path]);
      }
    }
    return $pathsAndOperations;
  }

  /**
   * Private.
   */
  private function modifySqlEndpoint($operations, $distribution) {
    $distKey = isset($distribution['data']['title']) ? $distribution['data']['title'] : $distribution['identifier'];
    unset($operations['get']['parameters'][0]['example']);
    $operations['get']['parameters'][0]['examples'][$distKey] = [
      "summary" => "Query distribution {$distribution['identifier']}",
      "value" => "[SELECT * FROM {$distribution['identifier']}][LIMIT 2]",
    ];
    return $operations;
  }

  /**
   * Get a dataset's resources/distributions.
   *
   * @param string $identifier
   *   The dataset uuid.
   *
   * @return array
   *   Modified spec.
   */
  private function getDistributions(string $identifier) {

    $data = $this->metastore->swapReferences($this->metastore->get("dataset", $identifier));

    return $data->{"$.distribution"} ?? [];
  }

}
