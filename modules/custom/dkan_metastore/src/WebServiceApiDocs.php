<?php

namespace Drupal\dkan_metastore;

use Drupal\dkan_common\DataModifierPluginTrait;
use Drupal\dkan_common\Plugin\DataModifierManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\dkan_common\JsonResponseTrait;
use Drupal\dkan_data\Reference\Dereferencer;
use Drupal\dkan_api\Controller\Docs;

/**
 * Provides dataset-specific OpenAPI documentation.
 */
class WebServiceApiDocs implements ContainerInjectionInterface {
  use JsonResponseTrait;
  use DataModifierPluginTrait;

  /**
   * List of endpoints to keep for dataset-specific docs.
   *
   * Any combination of a path and any of its operations not specifically listed
   * below will be discarded.
   *
   * @var array
   */
  private $endpointsToKeep = [
    '/api/1/metastore/schemas/dataset/items/{identifier}' => ['get'],
    '/api/1/datastore/sql' => ['get'],
  ];

  /**
   * OpenAPI spec for dataset-related endpoints.
   *
   * @var \Drupal\dkan_api\Controller\Docs
   */
  private $docsController;

  /**
   * Metastore service.
   *
   * @var \Drupal\dkan_metastore\Service
   */
  private $metastoreService;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new WebServiceApiDocs(
      $container->get("dkan_api.docs"),
      $container->get("dkan_metastore.service"),
      $container->get('plugin.manager.dkan_common.data_modifier')
    );
  }

  /**
   * Constructs a new WebServiceApiDocs.
   *
   * @param \Drupal\dkan_api\Controller\Docs $docsController
   *   Serves openapi spec for dataset-related endpoints.
   * @param \Drupal\dkan_metastore\Service $metastoreService
   *   Metastore service.
   * @param \Drupal\dkan_common\Plugin\DataModifierManager $pluginManager
   *   Metastore plugin manager.
   */
  public function __construct(Docs $docsController, Service $metastoreService, DataModifierManager $pluginManager) {
    $this->docsController = $docsController;
    $this->metastoreService = $metastoreService;
    $this->pluginManager = $pluginManager;

    $this->plugins = $this->discover();
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
    $spec = $this->keepDatasetSpecificEndpoints($fullSpec, $this->endpointsToKeep);
    // Remove the security schemes.
    unset($spec['components']['securitySchemes']);
    // Tags can be added later when needed, remove them for now.
    $spec['tags'] = [];

    $spec = $this->modifyDatasetEndpoint($spec, $identifier);
    $spec = $this->modifySqlEndpoint($spec, $identifier);
    return $this->getResponse($spec);
  }

  /**
   * Modify the generic dataset endpoint to be specific to the current dataset.
   *
   * @param array $spec
   *   The original spec.
   * @param string $identifier
   *   Dataset uuid.
   *
   * @return array
   *   Spec with dataset-specific metastore get endpoint.
   */
  private function modifyDatasetEndpoint(array $spec, string $identifier) {
    if (isset($spec['paths']['/api/1/metastore/schemas/dataset/items/{identifier}'])) {
      unset($spec['paths']['/api/1/metastore/schemas/dataset/items/{identifier}']['get']['parameters']);
      // Replace the dataset uuid placeholder.
      $spec['paths']['/api/1/metastore/schemas/dataset/items/' . $identifier] = $spec['paths']['/api/1/metastore/schemas/dataset/items/{identifier}'];
      unset($spec['paths']['/api/1/metastore/schemas/dataset/items/{identifier}']);
      // Keep only the tags needed, starting with the dataset tag.
      $spec['tags'][] = ["name" => "Dataset"];
    }
    return $spec;
  }

  /**
   * Provides data modifiers plugins an opportunity to act.
   *
   * @param string $identifier
   *   The distribution's identifier.
   *
   * @return bool
   *   TRUE if sql endpoint docs needs to be protected, FALSE otherwise.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  private function modifyData(string $identifier) {
    foreach ($this->plugins as $plugin) {
      if ($plugin->requiresModification('distribution', $identifier)) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Modify the generic sql endpoint to be specific to the current dataset.
   *
   * @param array $spec
   *   The original spec.
   * @param string $identifier
   *   Dataset uuid.
   *
   * @return array
   *   Spec with dataset-specific datastore sql endpoint.
   */
  private function modifySqlEndpoint(array $spec, string $identifier) {
    if ($this->modifyData($identifier)) {
      unset($spec['paths']['/api/1/datastore/sql']);
    }
    elseif (isset($spec['paths']['/api/1/datastore/sql'])) {
      unset($spec['paths']['/api/1/datastore/sql']['get']['parameters']);
      $spec = $this->replaceDistributions($spec, $identifier);
      $spec['tags'][] = ["name" => "SQL Query"];
    }
    return $spec;
  }

  /**
   * Keep only paths and operations relevant for our dataset-specific docs.
   *
   * @param array $spec
   *   The original spec array.
   * @param array $endpointsToKeep
   *   List of endpoints to keep.
   *
   * @return array
   *   Modified spec.
   */
  private function keepDatasetSpecificEndpoints(array $spec, array $endpointsToKeep) {
    $relevant_paths = array_keys($endpointsToKeep);
    foreach ($spec['paths'] as $path => $operations) {
      if (in_array($path, $relevant_paths)) {
        $this->filterOperationsInCurrentPath($operations, $path, $endpointsToKeep, $spec);
      }
      else {
        unset($spec['paths'][$path]);
      }
    }

    return $spec;
  }

  /**
   * Keep relevant operations on the current path.
   *
   * @param array $operations
   *   Operations for the current path.
   * @param string $path
   *   The path being processed.
   * @param array $endpointsToKeep
   *   Array of endpoints (paths with operations) to keep.
   * @param array $spec
   *   Our modified dataset-specific openapi spec.
   */
  private function filterOperationsInCurrentPath(array $operations, string $path, array $endpointsToKeep, array &$spec) {
    foreach ($operations as $operation => $details) {
      if (!in_array($operation, $endpointsToKeep[$path])) {
        unset($spec['paths'][$path][$operation]);
      }
    }
  }

  /**
   * Replace the sql {query} placeholder with dataset-specific distributions.
   *
   * @param array $spec
   *   The original spec array.
   * @param string $identifier
   *   The dataset uuid.
   *
   * @return array
   *   Modified spec.
   */
  private function replaceDistributions(array $spec, string $identifier) {
    // Load this dataset's metadata with both data and identifiers.
    if (function_exists('drupal_static')) {
      drupal_static('dkan_data_dereference_method', Dereferencer::DEREFERENCE_OUTPUT_REFERENCE_IDS);
    }

    $data = json_decode($this->metastoreService->get("dataset", $identifier));

    // Create and customize a path for each dataset distribution/resource.
    if (isset($data->distribution)) {
      foreach ($data->distribution as $dist) {
        $spec = $this->replaceDistribution($dist, $spec, $identifier);
      }
      unset($spec['paths']['/api/1/datastore/sql']);
    }
    return $spec;
  }

  /**
   * Replace a single distribution within the spec.
   *
   * @param mixed $dist
   *   A distribution object.
   * @param array $spec
   *   The original spec array.
   * @param string $identifier
   *   The dataset uuid.
   *
   * @return array
   *   Modified spec.
   */
  private function replaceDistribution($dist, array $spec, string $identifier) {
    $path = "/api/1/datastore/sql?query=[SELECT * FROM {$dist->identifier}];";

    $spec['paths'][$path] = $spec['paths']['/api/1/datastore/sql'];
    if (isset($dist->data->title)) {
      $spec['paths'][$path]['get']['summary'] = $dist->data->title;
    }
    if (isset($dist->data->description)) {
      $spec['paths'][$path]['get']['description'] = $dist->data->description;
    }
    return $spec;
  }

}
