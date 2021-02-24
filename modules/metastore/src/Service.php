<?php

namespace Drupal\metastore;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\common\DataModifierPluginTrait;
use Drupal\common\Plugin\DataModifierManager;
use Drupal\metastore\Exception\CannotChangeUuidException;
use Drupal\metastore\Exception\ExistingObjectException;
use Drupal\metastore\Exception\MissingObjectException;
use Drupal\metastore\Exception\UnmodifiedObjectException;
use Drupal\metastore\Storage\MetastoreStorageFactoryInterface;
use Drupal\metastore\Storage\MetastoreStorageInterface;
use RootedData\Exception\ValidationException;
use RootedData\RootedJsonData;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Service.
 */
class Service implements ContainerInjectionInterface {
  use DataModifierPluginTrait;

  /**
   * Schema retriever.
   *
   * @var \Drupal\metastore\SchemaRetrieverInterface
   */
  private $schemaRetriever;

  /**
   * Storage factory.
   *
   * @var \Drupal\metastore\Storage\MetastoreStorageFactoryInterface
   */
  private $storageFactory;

  /**
   * Storages.
   *
   * @var array
   */
  private $storages;

  /**
   * Inherited.
   *
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new Service(
      $container->get('metastore.schema_retriever'),
      $container->get('dkan.metastore.storage')
    );
  }

  /**
   * Constructor.
   */
  public function __construct(SchemaRetrieverInterface $schemaRetriever, MetastoreStorageFactoryInterface $factory) {
    $this->schemaRetriever = $schemaRetriever;
    $this->storageFactory = $factory;
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
   * Get storage.
   *
   * @param string $schema_id
   *   The {schema_id} slug from the HTTP request.
   *
   * @return \Drupal\metastore\Storage\MetastoreStorageInterface
   *   Entity storage.
   */
  private function getStorage(string $schema_id): MetastoreStorageInterface {
    if (!isset($this->storages[$schema_id])) {
      $this->storages[$schema_id] = $this->storageFactory->getInstance($schema_id);
    }
    return $this->storages[$schema_id];
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

    $datasets = $this->getStorage($schema_id)->retrieveAll();

    // $datasets is an array of JSON encoded string. Needs to be unflattened.
    $unflattened = array_map(
      function ($json_string) use ($schema_id) {
        if (!empty($this->plugins)) {
          $json_string = $this->modifyData($schema_id, $json_string);
        }
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
    $data = $this->getStorage($schema_id)->retrievePublished($identifier);
    if (!empty($this->plugins)) {
      $data = $this->modifyData($schema_id, $data);
    }
    return $data;
  }

  /**
   * Provides data modifiers plugins an opportunity to act.
   *
   * @param string $schema_id
   *   The {schema_id} slug from the HTTP request.
   * @param string $data
   *   The Json input.
   *
   * @return string
   *   The Json, modified by each applicable discovered data modifier plugins.
   */
  private function modifyData(string $schema_id, string $data) {
    $dataObj = json_decode($data);

    foreach ($this->plugins as $plugin) {
      if ($plugin->requiresModification($schema_id, $dataObj)) {
        $dataObj = $plugin->modify($schema_id, $dataObj);
      }
    }

    // TODO: abandon the method and use RootedJsonData instead on JSON string.
    $this->validateJson($schema_id, $data);

    return json_encode($dataObj);
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
    $json = $this->getStorage($schema_id)->retrieve($identifier);
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
    // TODO: abandon the method and use RootedJsonData instead on JSON string.
    $this->validateJson($schema_id, $data);

    $identifier = NULL;

    // If resource already exists, return HTTP 409 Conflict and existing uri.
    $decoded = json_decode($data, TRUE);
    if (isset($decoded['identifier'])) {
      $identifier = $decoded['identifier'];
      if ($this->objectExists($schema_id, $identifier)) {
        throw new ExistingObjectException("{$schema_id}/{$identifier} already exists.");
      }
    }

    return $this->getStorage($schema_id)->store($data, $identifier);
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
      return $this->getStorage($schema_id)->publish($identifier);
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
    // TODO: abandon the method and use RootedJsonData instead on JSON string.
    $this->validateJson($schema_id, $data);

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
      $this->getStorage($schema_id)->store($data, $identifier);
      return ['identifier' => $identifier, 'new' => FALSE];
    }
    else {
      $this->getStorage($schema_id)->store($data);
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
    // TODO: abandon the method and use RootedJsonData instead on JSON string.
    $this->validateJson($schema_id, $data);

    $storage = $this->getStorage($schema_id);
    if ($this->objectExists($schema_id, $identifier)) {
      $storage->store($data, $identifier);
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
    $storage = $this->getStorage($schema_id);

    $storage->remove($identifier);

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
      $this->getStorage($schemaId)->retrieve($identifier);
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
    $existingMetadata = $this->getStorage($schema_id)->retrieve($identifier);
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
   * Temporary validate method.
   *
   * Using RootedJsonData instead of JSON string will make it redundant.
   *
   * @param string $schema_id
   *   The {schema_id} slug from the HTTP request.
   * @param string $json_data
   *   Json payload.
   *
   * @return bool
   */
  private function validateJson(string $schema_id, string $json_data): bool {
    $schema = $this->schemaRetriever->retrieve($schema_id);
    $result = RootedJsonData::validate($json_data, $schema);
    if (!$result->isValid()) {
      throw new ValidationException("JSON Schema validation failed.", $result);
    }
    return TRUE;
  }

}
