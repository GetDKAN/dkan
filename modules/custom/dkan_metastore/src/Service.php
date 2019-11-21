<?php

namespace Drupal\dkan_metastore;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\dkan_metastore\Factory\Sae;
use Drupal\dkan_metastore\Exception\ObjectExists;
use Drupal\dkan_metastore\Exception\ObjectNotFound;
use Drupal\dkan_data\ValueReferencer;
use Drupal\dkan_schema\SchemaRetriever;

/**
 * Service.
 */
class Service implements ContainerInjectionInterface {

  /**
   * SAE Factory.
   *
   * @var \Drupal\dkan_metastore\Factory\Sae
   */
  private $saeFactory;

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
    return new Service(
      $container->get('dkan_schema.schema_retriever'),
      $container->get('dkan_metastore.sae_factory')
    );
  }

  /**
   * Constructor.
   */
  public function __construct(SchemaRetriever $schemaRetriever, Sae $saeFactory) {
    $this->schemaRetriever = $schemaRetriever;
    $this->saeFactory = $saeFactory;
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
    return $schemas;
  }

  /**
   * Get schema.
   */
  public function getSchema($identifier) {
    $schema = $this->schemaRetriever->retrieve($identifier);
    $schema = json_decode($schema);

    return $schema;
  }

  /**
   * Get all.
   *
   * @param string $schema_id
   *   The {schema_id} slug from the HTTP request.
   *
   * @return array
   *   All objects of the given schema_id.
   */
  public function getAll($schema_id): array {

    $datasets = $this->getEngine($schema_id)->get();

    // $datasets is an array of JSON encoded string. Needs to be unflattened.
    $unflattened = array_map(
      function ($json_string) {
        return json_decode($json_string);
      },
      $datasets
    );

    return $unflattened;
  }

  /**
   * Implements GET method.
   *
   * @param string $schema_id
   *   The {schema_id} slug from the HTTP request.
   * @param string $identifier
   *   Identifier.
   *
   * @return string
   *   The json data.
   */
  public function get($schema_id, $identifier): string {
    return $this->getEngine($schema_id)
      ->get($identifier);
  }

  /**
   * GET all resources associated with a dataset.
   *
   * @param string $schema_id
   *   The {schema_id} slug from the HTTP request.
   * @param string $identifier
   *   Identifier.
   *
   * @return array
   *   An array of resources.
   */
  public function getResources($schema_id, $identifier): array {

    // Load this dataset's metadata with both data and identifiers.
    if (function_exists('drupal_static')) {
      drupal_static('dkan_data_dereference_method', ValueReferencer::DEREFERENCE_OUTPUT_REFERENCE_IDS);
    }

    $json = $this->getEngine($schema_id)
      ->get($identifier);
    $data = json_decode($json);
    /* @todo decouple from POD. */
    $resources = $data->distribution;

    return $resources;
  }

  /**
   * Implements POST method.
   *
   * @param string $schema_id
   *   The {schema_id} slug from the HTTP request.
   * @param string $data
   *   Json payload.
   *
   * @return string
   *   The identifier.
   */
  public function post($schema_id, string $data): string {
    // If resource already exists, return HTTP 409 Conflict and existing uri.
    $decoded = json_decode($data, TRUE);
    if (isset($decoded['identifier'])) {
      $identifier = $decoded['identifier'];
      if ($this->objectExists($schema_id, $identifier)) {
        throw new ObjectExists("{$schema_id}/{$identifier} already exists.");
      }
    }

    return $this->getEngine($schema_id)->post($data);
  }

  /**
   * Implements PUT method.
   *
   * @param string $schema_id
   *   The {schema_id} slug from the HTTP request.
   * @param string $identifier
   *   Identifier.
   * @param string $data
   *   Json payload.
   *
   * @return array
   *   ["identifier" => string, "new" => boolean].
   */
  public function put($schema_id, $identifier, string $data): array {
    $new = TRUE;
    $engine = $this->getEngine($schema_id);

    $obj = json_decode($data);
    if (isset($obj->identifier) && $obj->identifier != $identifier) {
      throw new \Exception("Identifier cannot be modified");
    }

    if ($this->objectExists($schema_id, $identifier)) {
      $engine->put($identifier, $data);
      $new = FALSE;
    }
    else {
      $engine->post($data);
    }

    return ['identifier' => $identifier, 'new' => $new];
  }

  /**
   * Implements PATCH method.
   *
   * @param string $schema_id
   *   The {schema_id} slug from the HTTP request.
   * @param string $identifier
   *   Identifier.
   * @param mixed $data
   *   Json payload.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The json response.
   */
  public function patch($schema_id, $identifier, $data) {
    $engine = $this->getEngine($schema_id);
    if ($this->objectExists($schema_id, $identifier)) {
      $engine->patch($identifier, $data);
      return $identifier;
    }

    throw new ObjectNotFound("No data with the identifier {$identifier} was found.");
  }

  /**
   * Implements DELETE method.
   *
   * @param string $schema_id
   *   The {schema_id} slug from the HTTP request.
   * @param string $identifier
   *   Identifier.
   *
   * @return string
   *   Identifier.
   */
  public function delete($schema_id, $identifier) {
    $engine = $this->getEngine($schema_id);

    $engine->delete($identifier);

    return $identifier;
  }

  /**
   * Private.
   */
  private function objectExists($schemaId, $identifier) {
    try {
      $this->getEngine($schemaId)->get($identifier);
      return TRUE;
    }
    catch (\Exception $e) {
      return FALSE;
    }
  }

  /**
   * Get engine.
   */
  private function getEngine($schemaId) {
    return $this->saeFactory->getInstance($schemaId);
  }

}
