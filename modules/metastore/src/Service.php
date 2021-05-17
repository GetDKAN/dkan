<?php

namespace Drupal\metastore;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\common\EventDispatcherTrait;
use Drupal\metastore\Exception\CannotChangeUuidException;
use Drupal\metastore\Exception\ExistingObjectException;
use Drupal\metastore\Exception\MissingObjectException;
use Drupal\metastore\Exception\UnmodifiedObjectException;
use Drupal\metastore\Factory\Sae;
use Drupal\metastore\Storage\DataFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Service.
 */
class Service implements ContainerInjectionInterface {
  use EventDispatcherTrait;

  const EVENT_DATA_GET = 'dkan_metastore_data_get';
  const EVENT_DATA_GET_ALL = 'dkan_metastore_data_get_all';

  /**
   * SAE Factory.
   *
   * @var \Drupal\metastore\Factory\Sae
   */
  private $saeFactory;

  /**
   * Schema retriever.
   *
   * @var \Drupal\metastore\SchemaRetriever
   */
  private $schemaRetriever;

  /**
   * Storage.
   *
   * @var \Drupal\metastore\Storage\DataFactory
   */
  private $factory;

  /**
   * Inherited.
   *
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new Service(
      $container->get('dkan.metastore.schema_retriever'),
      $container->get('dkan.metastore.sae_factory'),
      $container->get('dkan.metastore.storage')
    );
  }

  /**
   * Constructor.
   */
  public function __construct(SchemaRetriever $schemaRetriever, Sae $saeFactory, DataFactory $factory) {
    $this->schemaRetriever = $schemaRetriever;
    $this->saeFactory = $saeFactory;
    $this->factory = $factory;
  }

  /**
   * Setter to discover data modifier plugins.
   *
   * @param \Drupal\common\Plugin\DataModifierManager $pluginManager
   *   Injected plugin manager.
   */
  public function setDataModifierPlugins(DataModifierManager $pluginManager) {
    $this->pluginManager = $pluginManager;
    $this->plugins = $this->discover();
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
    $jsonStringsArray = $this->getEngine($schema_id)->get();

    $objects = array_map(
      function ($jsonString) {
        try {
          return json_decode($this->dispatchEvent(self::EVENT_DATA_GET, $jsonString));
        }
        catch (\Exception $e) {
          return (object) ["message" => $e->getMessage()];
        }
      },
      $jsonStringsArray
    );

    $objects = $this->dispatchEvent(self::EVENT_DATA_GET_ALL, $objects);

    return $objects;
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
    $storage = $this->factory->getInstance($schema_id);
    $data = $storage->retrievePublished($identifier);
    $data = $this->dispatchEvent(self::EVENT_DATA_GET, $data);
    return $data;
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
   *
   * @todo Make this aware of revisions and moderation states.
   */
  public function getResources($schema_id, $identifier): array {
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
        throw new ExistingObjectException("{$schema_id}/{$identifier} already exists.");
      }
    }

    return $this->getEngine($schema_id)->post($data);
  }

  /**
   * Publish an item's update by making its latest revision its default one.
   *
   * @param string $schema_id
   *   The {schema_id} slug from the HTTP request.
   * @param string $identifier
   *   Identifier.
   *
   * @return string
   *   Identifier.
   */
  public function publish(string $schema_id, string $identifier) {
    if ($this->objectExists($schema_id, $identifier)) {
      $storage = $this->factory->getInstance($schema_id);
      return $storage->publish($identifier);
    }

    throw new MissingObjectException("No data with the identifier {$identifier} was found.");
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
    $obj = json_decode($data);
    if (isset($obj->identifier) && $obj->identifier != $identifier) {
      throw new CannotChangeUuidException("Identifier cannot be modified");
    }
    elseif ($this->objectExists($schema_id, $identifier) && $this->objectIsEquivalent($schema_id, $identifier, $data)) {
      throw new UnmodifiedObjectException("No changes to {$schema_id} with identifier {$identifier}.");
    }
    else {
      return $this->proceedWithPut($schema_id, $identifier, $data);
    }
  }

  /**
   * Proceed with PUT.
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
  private function proceedWithPut($schema_id, $identifier, string $data): array {
    if ($this->objectExists($schema_id, $identifier)) {
      $this->getEngine($schema_id)->put($identifier, $data);
      return ['identifier' => $identifier, 'new' => FALSE];
    }
    else {
      $this->getEngine($schema_id)->post($data);
      return ['identifier' => $identifier, 'new' => TRUE];
    }
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

    throw new MissingObjectException("No data with the identifier {$identifier} was found.");
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
   * Assembles the data catalog object.
   *
   * @return object
   *   The catalog object
   */
  public function getCatalog() {
    $catalog = $this->getSchema('catalog');
    $catalog->dataset = $this->getAll('dataset');

    return $catalog;
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
   * Verify if metadata is equivalent.
   *
   * Because json metadata strings could be formatted differently (white space,
   * order of properties...) yet be equivalent, compare their resulting json
   * objects.
   *
   * @param string $schema_id
   *   The {schema_id} slug from the HTTP request.
   * @param string $identifier
   *   The uuid.
   * @param string $metadata
   *   The new data being compared to the existing data.
   *
   * @return bool
   *   TRUE if the metadata is equivalent, false otherwise.
   */
  private function objectIsEquivalent(string $schema_id, string $identifier, string $metadata) {
    $existingMetadata = $this->getEngine($schema_id)->get($identifier);
    $existing = json_decode($existingMetadata);
    $existing = self::removeReferences($existing);
    $new = json_decode($metadata);
    return $new == $existing;
  }

  /**
   * Private.
   */
  public static function removeReferences($object, $prefix = "%") {
    $array = (array) $object;
    foreach ($array as $property => $value) {
      if (substr_count($property, $prefix) > 0) {
        unset($array[$property]);
      }
    }

    $object = (object) $array;

    if (isset($object->distribution[0]->{"%Ref:downloadURL"})) {
      unset($object->distribution[0]->{"%Ref:downloadURL"});
    }

    return $object;
  }

  /**
   * Get engine.
   */
  private function getEngine($schemaId) {
    return $this->saeFactory->getInstance($schemaId);
  }

}
