<?php

namespace Drupal\metastore;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\common\JsonResponseTrait;
use Drupal\common\Docs;

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
  private $endpointsToKeep = [
    'metastore/schemas/dataset/items/{identifier}' => ['get'],
    'datastore/sql' => ['get'],
  ];

  /**
   * OpenAPI spec for dataset-related endpoints.
   *
   * @var \Drupal\common\Docs
   */
  private $docsController;

  /**
   * Metastore service.
   *
   * @var \Drupal\metastore\Service
   */
  private $metastoreService;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new WebServiceApiDocs(
      $container->get("common.docs"),
      $container->get('dkan.metastore.service'),
    );
  }

  /**
   * Constructs a new WebServiceApiDocs.
   *
   * @param \Drupal\common\Docs $docsController
   *   Serves openapi spec for dataset-related endpoints.
   * @param \Drupal\metastore\Service $metastoreService
   *   Metastore service.
   */
  public function __construct(Docs $docsController, Service $metastoreService) {
    $this->docsController = $docsController;
    $this->metastoreService = $metastoreService;
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
    $fullSpec = $this->docsController->getJsonFromYmlFile();

    // Remove the security schemes.
    unset($fullSpec['components']['securitySchemes']);

    // Tags can be added later when needed, remove them for now.
    $fullSpec['tags'] = [];

    $pathsAndOperations = $fullSpec['paths'];
    $pathsAndOperations = $this->keepDatasetSpecificEndpoints($pathsAndOperations);
    $pathsAndOperations = $this->modifyDatasetEndpoints($pathsAndOperations, $identifier);
    $pathsAndOperations = $this->modifySqlEndpoints($pathsAndOperations, $identifier);

    $fullSpec['paths'] = $pathsAndOperations;
    return $this->getResponse($fullSpec);
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
   * Modify the generic dataset endpoint to be specific to the current dataset.
   *
   * @param array $pathsAndOperations
   *   The paths defined in the original spec.
   * @param string $identifier
   *   Dataset uuid.
   *
   * @return array
   *   Spec with dataset-specific metastore get endpoint.
   */
  private function modifyDatasetEndpoints(array $pathsAndOperations, string $identifier) {

    foreach ($pathsAndOperations as $path => $operations) {
      $newPath = $path;
      $newOperations = $operations;
      unset($pathsAndOperations[$path]);
      [$newPath, $newOperations] = $this->modifyDatasetEndpoint($newPath, $newOperations, $identifier);
      $pathsAndOperations[$newPath] = $newOperations;
    }

    return $pathsAndOperations;
  }

  /**
   * Private.
   */
  private function modifyDatasetEndpoint($path, $operations, $identifier) {
    $newOperations = $this->getModifyDatasetEndpointNewOperations($operations, $identifier);

    if (!isset($newOperations)) {
      return [$path, $operations];
    }

    $newPath = str_replace("{identifier}", $identifier, $path);

    return [$newPath, $newOperations];
  }

  /**
   * Private.
   */
  private function getModifyDatasetEndpointNewOperations($operations, $identifier) {
    $modified = FALSE;
    foreach ($operations as $operation => $info) {
      $newParameters = $this->getModifyDatasetEndpointNewParameters($info['parameters'], $identifier);
      if ($newParameters) {
        $operations[$operation]['parameters'] = $newParameters;
        $modified = TRUE;
      }
    }

    return ($modified) ? $operations : NULL;
  }

  /**
   * Private.
   */
  private function getModifyDatasetEndpointNewParameters(array $parameters, $identifier): ?array {
    $modified = FALSE;
    foreach ($parameters as $key => $parameter) {
      if (isset($parameter['name']) && $parameter['name'] == "identifier" && isset($parameter['example'])) {
        $parameters[$key]['example'] = $identifier;
        $modified = TRUE;
      }
    }
    return ($modified) ? $parameters : NULL;
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
        list($newPath, $newOperations) = $this->modifySqlEndpoint($path, $operations, $dist);
        $pathsAndOperations[$newPath] = $newOperations;
      }

      unset($pathsAndOperations[$path]);
    }

    return $pathsAndOperations;
  }

  /**
   * Private.
   */
  private function getSqlPathsAndOperations($pathsAndOperations) {
    foreach ($pathsAndOperations as $path => $operations) {
      if (substr_count($path, 'sql') == 0) {
        unset($pathsAndOperations[$path]);
      }
    }
    return $pathsAndOperations;
  }

  /**
   * Private.
   */
  private function modifySqlEndpoint($path, $operations, $distribution) {
    $newPath = "{$path}?query=[SELECT * FROM {$distribution->identifier}];";

    // Replace schema's "DATASTORE-UUID" with the distribution's identifier.
    $operations['get']['parameters'][0]['example'] = str_replace(
      "DATASTORE-UUID",
      $distribution->identifier,
      $operations['get']['parameters'][0]['example']
    );

    return [$newPath, $operations];
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

    $data = json_decode($this->metastoreService->get("dataset", $identifier));

    // Create and customize a path for each dataset distribution/resource.
    $distributionRefProperty = "%Ref:distribution";
    if (isset($data->{$distributionRefProperty})) {
      return $data->{$distributionRefProperty};
    }
    return [];
  }

}
