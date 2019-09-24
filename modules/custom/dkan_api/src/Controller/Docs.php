<?php

declare(strict_types = 1);

namespace Drupal\dkan_api\Controller;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Serialization\Yaml;
use Drupal\dkan_data\Storage\Data;
use Drupal\dkan_data\ValueReferencer;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Serves openapi spec for dataset-related endpoints.
 */
class Docs implements ContainerInjectionInterface {

  /**
   * The API array spec, to ease manipulation, before json encoding.
   *
   * @var array
   */
  private $spec;

  /**
   * Module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  private $moduleHandler;

  /**
   * Serializer to translate yaml to json.
   *
   * @var \Symfony\Component\Serializer\Serializer
   */
  private $serializer;

  /**
   * Drupal node dataset storage.
   *
   * @var \Drupal\dkan_api\Storage\Data
   */
  private $storage;

  /**
   * Inherited.
   *
   * @{inheritdocs}
   *
   * @codeCoverageIgnore
   */
  public static function create(ContainerInterface $container) {
    $moduleHandler = $container->get('module_handler');
    $storage = $container->get('dkan_data.storage');
    return new Docs($moduleHandler, $storage);
  }

  /**
   * Constructor.
   */
  public function __construct(
    ModuleHandlerInterface $moduleHandler,
    Data $storage
  ) {
    $this->moduleHandler = $moduleHandler;
    $this->serializer = new Yaml();
    $this->storage = $storage;
    $this->storage->setSchema('dataset');
    $this->spec = $this->getJsonFromYmlFile();
  }

  /**
   * Load the yaml spec file and convert it to an array.
   *
   * @return array
   *   The openapi spec.
   */
  private function getJsonFromYmlFile() {
    $modulePath = $this->moduleHandler->getModule('dkan_api')->getPath();
    $ymlSpecPath = $modulePath . '/docs/dkan_api_openapi_spec.yml';
    $ymlSpec = $this->fileGetContents($ymlSpecPath);

    return $this->serializer->decode($ymlSpec);
  }

  /**
   * Wrapper around file_get_contents to facilitate testing.
   *
   * @param string $path
   *   Path for our yml spec.
   *
   * @return false|string
   *   Our yml file, or FALSE.
   *
   * @codeCoverageIgnore
   */
  private function fileGetContents($path) {
    return file_get_contents($path);
  }

  /**
   * Returns the complete API spec.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   OpenAPI spec response.
   */
  public function getComplete() {
    $jsonSpec = json_encode($this->spec);

    return $this->sendResponse($jsonSpec);
  }

  /**
   * Returns only dataset-specific GET requests for the API spec.
   *
   * @param \Drupal\dkan_api\Controller\string $uuid
   *   Dataset uuid.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   OpenAPI spec response.
   */
  public function getDatasetSpecific(string $uuid) {
    // Keep only the GET requests.
    $spec = $this->removeSpecOperations(
          $this->spec, [
            'post',
            'put',
            'patch',
            'delete',
          ]
      );
    // Remove GET dataset collection endpoint as well as property-related ones.
    // @TODO: consider flipping the logic, keeping array of paths interested in.
    $spec = $this->removeSpecPaths(
          $spec, [
            '/api/v1/dataset',
            '/api/v1/{property}',
            '/api/v1/{property}/{uuid}',
            '/api/v1/harvest',
            '/api/v1/harvest/info/{id}',
            '/api/v1/harvest/info/{id}/{run_id}',
            '/api/v1/docs',
            '/api/v1/docs/{uuid}',
            '/api/v1/datastore',
            '/api/v1/datastore/{uuid}',
          ]
      );
    // Remove the security schemes.
    unset($spec['components']['securitySchemes']);
    // Remove required parameters, since now part of path.
    unset($spec['paths']['/api/v1/sql/{query}']['get']['parameters']);
    unset($spec['paths']['/api/v1/dataset/{uuid}']['get']['parameters']);
    // Keep only the tags needed, so remove the properties tag.
    $spec['tags'] = [
      ["name" => "Dataset"],
      ["name" => "SQL Query"],
    ];
    // Replace the dataset uuid placeholder.
    if (isset($spec['paths']['/api/v1/dataset/{uuid}'])) {
      $spec['paths']['/api/v1/dataset/' . $uuid] = $spec['paths']['/api/v1/dataset/{uuid}'];
      unset($spec['paths']['/api/v1/dataset/{uuid}']);
    }

    // Replace the sql endpoint query placeholder.
    $spec = $this->replaceDistributions($spec, $uuid);

    $jsonSpec = json_encode($spec);
    return $this->sendResponse($jsonSpec);
  }

  /**
   * Helper function to set headers and send response.
   *
   * @param string $jsonSpec
   *   OpenAPI spec encoded json response.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   OpenAPI spec response.
   */
  private function sendResponse(string $jsonSpec) {
    return new JsonResponse(
              $jsonSpec,
              200,
              [
                'Content-type' => 'application/json',
                'Access-Control-Allow-Origin' => '*',
              ],
              TRUE
          );
  }

  /**
   * Removes operations from the api spec's paths.
   *
   * @param array $spec
   *   The original spec array.
   * @param array $ops_to_remove
   *   Array of operations to be removed.
   *
   * @return array
   *   Modified spec.
   */
  private function removeSpecOperations(array $spec, array $ops_to_remove) {
    if (isset($spec['paths'])) {
      foreach ($spec['paths'] as $path => $operations) {
        foreach ($operations as $op => $details) {
          if (in_array($op, $ops_to_remove)) {
            unset($spec['paths'][$path][$op]);
          }
        }
        if (empty($spec['paths'][$path])) {
          unset($spec['paths'][$path]);
        }
      }
    }

    return $spec;
  }

  /**
   * Remove paths from the api spec.
   *
   * @param array $spec
   *   The original spec array.
   * @param array $paths_to_remove
   *   Array of paths to be removed.
   *
   * @return array
   *   Modified spec.
   */
  private function removeSpecPaths(array $spec, array $paths_to_remove) {
    if (!isset($spec['paths'])) {
      return $spec;
    }
    foreach ($spec['paths'] as $path => $ops) {
      if (in_array($path, $paths_to_remove)) {
        unset($spec['paths'][$path]);
      }
    }

    return $spec;
  }

  /**
   * Replace the sql {query} placeholder with dataset-specific distributions.
   *
   * @param array $spec
   *   The original spec array.
   * @param \Drupal\dkan_api\Controller\string $uuid
   *   The dataset uuid.
   *
   * @return array
   *   Modified spec.
   */
  private function replaceDistributions(array $spec, string $uuid) {
    // Load this dataset's metadata with both data and identifiers.
    if (function_exists('drupal_static')) {
      drupal_static('dkan_data_dereference_method', ValueReferencer::DEREFERENCE_OUTPUT_VERBOSE);
    }
    $dataset = $this->storage->retrieve($uuid);
    $data = json_decode($dataset);

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
