<?php

namespace Drupal\dkan_metastore\Controller;

use Drupal\dkan_api\Controller\Docs;
use Drupal\dkan_data\ValueReferencer;
use Sae\Sae;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\dkan_data\Storage\Data;
use Drupal\dkan_schema\SchemaRetriever;

/**
 * Class Api.
 */
class Api implements ContainerInjectionInterface {

  /**
   * Request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  private $requestStack;

  /**
   * Storage.
   *
   * @var \Drupal\dkan_data\Storage\Data
   */
  private $storage;

  /**
   * Schema retriever.
   *
   * @var \Drupal\dkan_schema\SchemaRetriever
   */
  private $schemaRetriever;

  /**
   * Inherited.
   *
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new Api($container->get('request_stack'),
      $container->get('dkan_schema.schema_retriever'),
      $container->get('dkan_data.storage')
    );
  }

  /**
   * Constructor.
   */
  public function __construct(RequestStack $requestStack, SchemaRetriever $schemaRetriever, Data $storage) {
    $this->requestStack = $requestStack;
    $this->schemaRetriever = $schemaRetriever;
    $this->storage = $storage;
  }

  /**
   * Get schemas.
   */
  public function getSchemas() {
    $schemas = [];
    foreach ($this->schemaRetriever->getAllIds() as $id) {
      $schema = $this->schemaRetriever->retrieve($id);
      $schemas[$id] = json_decode($schema);
    }
    return new JsonResponse($schemas);
  }

  /**
   * Get schema.
   */
  public function getSchema($identifier) {

    $schema = $this->schemaRetriever->retrieve($identifier);
    $schema = json_decode($schema);

    return new JsonResponse($schema);
  }

  /**
   * Get all.
   *
   * @param string $schema_id
   *   The {schema_id} slug from the HTTP request.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The json response.
   */
  public function getAll($schema_id) {

    $datasets = $this->getEngine($schema_id)->get();

    // $datasets is an array of JSON encoded string. Needs to be unflattened.
    $unflattened = array_map(
      function ($json_string) {
          return json_decode($json_string);
      },
      $datasets
    );

    return new JsonResponse(
      $unflattened,
      200,
      ["Access-Control-Allow-Origin" => "*"]
    );
  }

  /**
   * Implements GET method.
   *
   * @param string $schema_id
   *   The {schema_id} slug from the HTTP request.
   * @param string $identifier
   *   Identifier.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The json response.
   */
  public function get($schema_id, $identifier) {

    try {

      $data = $this->getEngine($schema_id)
        ->get($identifier);

      return new JsonResponse(
        json_decode($data),
        200,
        ["Access-Control-Allow-Origin" => "*"]
      );
    }
    catch (\Exception $e) {
      return new JsonResponse((object) ["message" => $e->getMessage()], 404);
    }
  }

  /**
   * GET all resources associated with a dataset.
   *
   * @param string $schema_id
   *   The {schema_id} slug from the HTTP request.
   * @param string $identifier
   *   Identifier.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The json response.
   */
  public function getResources($schema_id, $identifier) {

    try {
      // Load this dataset's metadata with both data and identifiers.
      if (function_exists('drupal_static')) {
        drupal_static('dkan_data_dereference_method', ValueReferencer::DEREFERENCE_OUTPUT_VERBOSE);
      }

      $json = $this->getEngine($schema_id)
        ->get($identifier);
      $data = json_decode($json);
      $distribution = $data->distribution;

      return new JsonResponse(
        $distribution,
        200,
        ["Access-Control-Allow-Origin" => "*"]
      );
    }
    catch (\Exception $e) {
      return new JsonResponse((object) ["message" => $e->getMessage()], 404);
    }
  }

  /**
   * Implements POST method.
   *
   * @param string $schema_id
   *   The {schema_id} slug from the HTTP request.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The json response.
   */
  public function post($schema_id) {
    $uri = $this->requestStack->getCurrentRequest()->getRequestUri();
    $data = $this->requestStack->getCurrentRequest()->getContent();

    // If resource already exists, return HTTP 409 Conflict and existing uri.
    $params = json_decode($data, TRUE);
    if (isset($params['identifier'])) {
      $identifier = $params['identifier'];
      if ($this->objectExists($schema_id, $identifier)) {
        return $this->getResponse(["endpoint" => "{$uri}/{$identifier}"], 409);
      }
    }

    try {
      $identifier = $this->getEngine($schema_id)->post($data);
      return $this->getResponse(["endpoint" => "{$uri}/{$identifier}", "identifier" => $identifier], 201);
    }
    catch (\Exception $e) {
      return $this->getResponseFromException($e, 406);
    }
  }

  /**
   * Implements PUT method.
   *
   * @param string $schema_id
   *   The {schema_id} slug from the HTTP request.
   * @param string $identifier
   *   Identifier.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The json response.
   */
  public function put($schema_id, $identifier) {
    /* @var $engine \Sae\Sae */
    $engine = $this->getEngine($schema_id);

    $data = $this->requestStack->getCurrentRequest()->getContent();

    $obj = json_decode($data);
    if (isset($obj->identifier) && $obj->identifier != $identifier) {
      return $this->getResponse(["message" => "Identifier cannot be modified"], 409);
    }

    $uri = $this->requestStack->getCurrentRequest()->getRequestUri();

    try {
      if ($this->objectExists($schema_id, $identifier)) {
        $engine->put($identifier, $data);
        return $this->getResponse(["endpoint" => "{$uri}", "identifier" => $identifier], 200);
      }
      else {
        $engine->post($data);
        return $this->getResponse(["endpoint" => "{$uri}", "identifier" => $identifier], 201);
      }
    }
    catch (\Exception $e) {
      return new JsonResponse((object) ["message" => $e->getMessage()], 406);
    }
  }

  /**
   * Private.
   */
  private function objectExists($schemaId, $identifier) {
    try {
      $this->getStorage($schemaId)->retrieve($identifier);
      return TRUE;
    }
    catch (\Exception $e) {
      return FALSE;
    }
  }

  /**
   * Implements PATCH method.
   *
   * @param string $schema_id
   *   The {schema_id} slug from the HTTP request.
   * @param string $identifier
   *   Identifier.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The json response.
   */
  public function patch($schema_id, $identifier) {

    /* @var $engine \Sae\Sae */
    $engine = $this->getEngine($schema_id);

    $data = $this->requestStack->getCurrentRequest()->getContent();

    try {
      $this->checkData($identifier, $data);
      if ($this->objectExists($schema_id, $identifier)) {
        $engine->patch($identifier, $data);
        $uri = $this->requestStack->getCurrentRequest()->getRequestUri();
        return $this->getResponse(["endpoint" => "{$uri}", "identifier" => $identifier], 200);
      }
      else {
        return $this->getResponse(["message" => "No data with the identifier {$identifier} was found."], 404);
      }
    }
    catch (InvalidPayloadException $e) {
      return $this->getResponse(["message" => $e->getMessage()], 409);
    }
    catch (\Exception $e) {
      return $this->getResponse(["message" => $e->getMessage()], 406);
    }
  }

  /**
   * Private.
   */
  private function checkData($identifier, $data) {
    $obj = json_decode($data);

    if (!$obj) {
      throw new InvalidPayloadException("Invalid JSON");
    }

    if (isset($obj->identifier) && $obj->identifier != $identifier) {
      throw new InvalidPayloadException("Identifier cannot be modified");
    }
  }

  /**
   * Implements DELETE method.
   *
   * @param string $schema_id
   *   The {schema_id} slug from the HTTP request.
   * @param string $identifier
   *   Identifier.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The json response.
   */
  public function delete($schema_id, $identifier) {
    /* @var $engine \Sae\Sae */
    $engine = $this->getEngine($schema_id);

    $engine->delete($identifier);

    return new JsonResponse((object) ["message" => "Dataset {$identifier} has been deleted."], 200);
  }

  /**
   * Get SAE instance.
   *
   * @return \Sae\Sae
   *   Service Api Engine
   */
  public function getEngine($schema_id) {
    return new Sae($this->getStorage($schema_id), $this->getJsonSchema($schema_id));
  }

  /**
   * Get Storage.
   *
   * @return \Drupal\dkan_api\Storage\Data
   *   Dataset
   */
  private function getStorage($schema_id) {
    $this->storage->setSchema($schema_id);
    return $this->storage;
  }

  /**
   * Get Json Schema.
   *
   * @return string
   *   Json schema.
   */
  private function getJsonSchema($schema_id) {

    // @Todo: mechanism to validate against additional schemas. For now,
    // validate against the empty object, as it accepts any valid json.
    if ($schema_id != 'dataset') {
      return '{ }';
    }

    return $this->schemaRetriever->retrieve('dataset');
  }

  /**
   * Private.
   */
  private function getResponse(array $message, int $code) {
    return new JsonResponse((object) $message, $code, ["Access-Control-Allow-Origin" => "*"]);
  }

  /**
   * Private.
   */
  private function getResponseFromException(\Exception $e, int $code) {
    return new JsonResponse((object) ['message' => $e->getMessage()], $code, ["Access-Control-Allow-Origin" => "*"]);
  }

  /**
   * Returns only dataset-specific GET requests for the API spec.
   *
   * @param \Drupal\dkan_api\Controller\string $identifier
   *   Dataset uuid.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   OpenAPI spec response.
   */
  public function getDatasetSpecific(string $identifier) {
    $spec = (Docs::create(\Drupal::getContainer()))->getJsonFromYmlFile();

    // Keep only the GET requests.
    $spec = $this->removeSpecOperations(
      $spec, [
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
        '/api/1/metastore/schemas/dataset',
        '/api/1/metastore/schemas/dataset/items',
        '/api/1/metastore/schemas/{schema_id}/items',
        '/api/1/metastore/schemas/{schema_id}/items/{identifier}',
        '/api/1/harvest/plans',
        '/api/1/harvest/plans/{plan_id}',
        '/api/1/harvest/runs',
        '/api/1/harvest/runs/{run_id}',
        '/api/1/datastore/imports',
        '/api/1/datastore/imports/{identifier}',
        '/api/1',
        '/api/1/metastore/schemas/dataset/items/{identifier}/docs',
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
      $spec['paths']['/api/v1/dataset/' . $identifier] = $spec['paths']['/api/v1/dataset/{uuid}'];
      unset($spec['paths']['/api/v1/dataset/{uuid}']);
    }

    // Replace the sql endpoint query placeholder.
    $spec = $this->replaceDistributions($spec, $identifier);

    $jsonSpec = json_encode($spec);
    return $this->sendResponse($jsonSpec);
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
   * @param \Drupal\dkan_api\Controller\string $identifier
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

    $storage = $this->getStorage('dataset');
    $dataset = $storage->retrieve($identifier);
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

}
