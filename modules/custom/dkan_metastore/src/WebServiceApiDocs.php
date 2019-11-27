<?php

namespace Drupal\dkan_metastore;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\dkan_common\JsonResponseTrait;
use Drupal\dkan_data\ValueReferencer;
use Drupal\dkan_api\Controller\Docs;

/**
 * Provides dataset-specific OpenAPI documentation.
 */
class WebServiceApiDocs implements ContainerInjectionInterface {
  use JsonResponseTrait;

  /**
   * List of endpoints to keep for dataset-specific docs.
   *
   * Any combination of a path and any of its verbs not specifically listed
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
      $container->get("dkan_metastore.service")
    );
  }

  /**
   * Constructs a new WebServiceApiDocs.
   *
   * @param \Drupal\dkan_api\Controller\Docs $docsController
   *   Serves openapi spec for dataset-related endpoints.
   * @param \Drupal\dkan_metastore\Service $metastoreService
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
    $spec = $this->docsController->getJsonFromYmlFile();

    $spec = $this->keepDatasetSpecificEndpoints($spec);

    // Remove the security schemes.
    unset($spec['components']['securitySchemes']);
    // Remove required parameters, since now part of path.
    unset($spec['paths']['/api/v1/sql/{query}']['get']['parameters']);
    unset($spec['paths']['/api/1/metastore/schemas/dataset/items/{identifier}']['get']['parameters']);
    // Keep only the tags needed, so remove the properties tag.
    $spec['tags'] = [
      ["name" => "Dataset"],
      ["name" => "SQL Query"],
    ];
    // Replace the dataset uuid placeholder.
    if (isset($spec['paths']['/api/1/metastore/schemas/dataset/items/{identifier}'])) {
      $spec['paths']['/api/1/metastore/schemas/dataset/items/' . $identifier] = $spec['paths']['/api/1/metastore/schemas/dataset/items/{identifier}'];
      unset($spec['paths']['/api/1/metastore/schemas/dataset/items/{identifier}']);
    }

    // Replace the sql endpoint query placeholder.
    $spec = $this->replaceDistributions($spec, $identifier);

    return $this->getResponse($spec);
  }

  /**
   * Keep only paths and verbs relevant for our dataset-specific docs.
   *
   * @param array $spec
   *   The original spec array.
   *
   * @return array
   *   Modified spec.
   */
  private function keepDatasetSpecificEndpoints(array $spec) {
    foreach ($spec['paths'] as $path => $operations) {
      // Discard unnecessary paths.
      if (empty($this->endpointsToKeep[$path])) {
        unset($spec['paths'][$path]);
      }
      else {
        $this->filterOperationsInCurrentPath($operations, $path, $spec);
      }
    }

    return $spec;
  }

  /**
   * Filter the verbs on the current path.
   *
   * @param array $operations
   *   Operations (verbs) for the current path.
   * @param string $path
   *   The path being processed.
   * @param array $spec
   *   Our modified dataset-specific openapi spec.
   */
  private function filterOperationsInCurrentPath(array $operations, string $path, array &$spec) {
    // Otherwise, discard unnecessary verbs.
    foreach ($operations as $operation => $details) {
      if (!in_array($operation, $this->endpointsToKeep[$path])) {
        unset($spec['paths'][$path][$operation]);
      }
    }
    // Discard any newly-empty paths.
    if (empty($spec['paths'][$path])) {
      unset($spec['paths'][$path]);
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
      drupal_static('dkan_data_dereference_method', ValueReferencer::DEREFERENCE_OUTPUT_REFERENCE_IDS);
    }

    $data = json_decode($this->metastoreService->get("dataset", $identifier));

    // Create and customize a path for each dataset distribution/resource.
    if (isset($data->distribution)) {
      foreach ($data->distribution as $dist) {
        $path = "/api/v1/sql/[SELECT * FROM {$dist->identifier}];";

        $spec['paths'][$path] = $spec['paths']['/api/v1/sql/{query}'];
        $spec['paths'][$path]['get']['summary'] = $dist->data->title ?? "";
        $spec['paths'][$path]['get']['description'] = $dist->data->description ?? "";
      }
      unset($spec['paths']['/api/v1/sql/{query}']);
    }
    return $spec;
  }

}
