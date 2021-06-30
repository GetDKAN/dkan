<?php

namespace Drupal\metastore;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\common\JsonResponseTrait;
use Drupal\common\DkanApiDocsGenerator;

/**
 * Provides dataset-specific OpenAPI documentation.
 */
class WebServiceApiDocs implements ContainerInjectionInterface {
  use JsonResponseTrait;

  /**
   * List of endpoints to keep for dataset-specific docs.
   *
   * Any combination of a path and any of its operations not specifically listed
   * below will be discarded.
   *
   * @var array
   */
  private $endpointsToKeep;

  /**
   * OpenAPI spec for dataset-related endpoints.
   *
   * @var \Drupal\common\DkanApiDocsGenerator
   */
  private $docsGenerator;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new WebServiceApiDocs(
      $container->get('dkan.common.docs_generator'),
      $container->get('dkan.metastore.service'),
    );
  }

  /**
   * Constructs a new WebServiceApiDocs.
   *
   * @param \Drupal\common\DkanApiDocsGenerator $docsGenerator
   *   Serves openapi spec.
   * @param \Drupal\metastore\Service $metastore
   *   The metastore service.
   */
  public function __construct(DkanApiDocsGenerator $docsGenerator, Service $metastore) {
    $this->docsGenerator = $docsGenerator;
    $this->metastore = $metastore;

    $this->endpointsToKeep = [
      'metastore/schemas/dataset/items/{identifier}' => ['get'],
      'datastore/sql' => ['get'],
    ];

    $this->parametersToKeep = ['datasetUuid', 'showReferenceIds'];
    $this->schemasToKeep = ['dataset', 'errorResponse'];
  }

  /**
   * Returns only dataset-specific GET requests for the API spec.
   *
   * @param string $identifier
   *   Dataset uuid.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   OpenAPI spec response.
   */
  public function getDatasetSpecific(string $identifier) {
    $fullSpec = $this->docsGenerator->buildSpec(
      ['metastore_api_docs', 'datastore_api_docs']
    )->{"$"};

    // Remove the security schemes.
    unset($fullSpec['components']['securitySchemes']);

    // Tags can be added later when needed, remove them for now.
    unset($fullSpec['tags']);

    $pathsAndOperations = $fullSpec['paths'];
    $pathsAndOperations = $this->keepDatasetSpecificEndpoints($pathsAndOperations);
    $pathsAndOperations = $this->modifySqlEndpoints($pathsAndOperations, $identifier);
    $parameters = $this->datasetSpecificParameters($fullSpec['components']['parameters'], $identifier);
    $schemas = $this->datasetSpecificSchemas($fullSpec['components']['schemas']);
    $responses = ["404IdNotFound" => $fullSpec["components"]["responses"]["404IdNotFound"]];

    $fullSpec['paths'] = $pathsAndOperations;
    $fullSpec['components']['parameters'] = $parameters;
    $fullSpec['components']['responses'] = $responses;
    $fullSpec['components']['schemas'] = $schemas;

    return $this->getResponse($fullSpec);
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
      if (in_array($key, $this->schemasToKeep)) {
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
      if (in_array($key, $this->parametersToKeep)) {
        return TRUE;
      }
      return FALSE;
    }, ARRAY_FILTER_USE_KEY);
    $newParameters['datasetUuid']['example'] = $identifier;
    return $newParameters;
  }

  /**
   * Keep only paths and operations relevant for our dataset-specific docs.
   *
   * @param array $pathsAndOperations
   *   The paths defined in the original spec.
   *
   * @return array
   *   Modified paths and operations array.
   */
  private function keepDatasetSpecificEndpoints(array $pathsAndOperations) {
    $keepPaths = array_keys($this->endpointsToKeep);

    $paths = array_keys($pathsAndOperations);

    $pathsToKeepPaths = array_combine($paths, array_map(function ($path) use ($keepPaths) {
      foreach ($keepPaths as $keepPath) {
        if (substr_count($path, $keepPath) > 0 && substr_count($path, $keepPath . "/") == 0) {
          return $keepPath;
        }
      }
      return NULL;
    }, $paths));

    foreach ($pathsAndOperations as $path => $operations) {
      if (is_null($pathsToKeepPaths[$path])) {
        unset($pathsAndOperations[$path]);
      }
      else {
        $pathsAndOperations[$path] = array_filter($operations, function ($operation) use ($path, $pathsToKeepPaths) {
          return in_array($operation, $this->endpointsToKeep[$pathsToKeepPaths[$path]]);
        }, ARRAY_FILTER_USE_KEY);
      }
    }

    return $pathsAndOperations;
  }

  /**
   * Modify the generic sql endpoint to be specific to the current dataset.
   *
   * @param array $pathsAndOperations
   *   The paths defined in the original spec.
   * @param string $identifier
   *   Dataset uuid.
   *
   * @return array
   *   Spec with dataset-specific datastore sql endpoint.
   */
  private function modifySqlEndpoints(array $pathsAndOperations, string $identifier) : array {

    foreach ($this->getSqlPathsAndOperations($pathsAndOperations) as $path => $operations) {

      foreach ($this->getDistributions($identifier) as $dist) {
        $newOperations = $this->modifySqlEndpoint($operations, $dist);
        $pathsAndOperations[$path] = $newOperations;
      }
    }

    return $pathsAndOperations;
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
    $distKey = isset($distribution->title) ? $distribution->title : $distribution->identifier;
    unset($operations['get']['parameters'][0]['example']);
    $operations['get']['parameters'][0]['examples'][$distKey] = [
      "summary" => "Query distribution {$distribution->identifier}",
      "value" => "[SELECT * FROM {$distribution->identifier}][LIMIT 2]",
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

    $data = json_decode($this->metastore->get("dataset", $identifier));

    // Create and customize a path for each dataset distribution/resource.
    $distributionRefProperty = "%Ref:distribution";
    if (isset($data->{$distributionRefProperty})) {
      return $data->{$distributionRefProperty};
    }
    return [];
  }

}
