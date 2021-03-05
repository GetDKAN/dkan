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
        $data = $this->jsonStringToRootedJsonData($schema_id, $json_string);
        if (!empty($this->plugins)) {
          $data = $this->modifyData($schema_id, $data);
        }
        return $data;
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
   * @return \RootedData\RootedJsonData
   *   The json data.
   */
  public function get($schema_id, $identifier): RootedJsonData {
    $json_string = $this->getStorage($schema_id)->retrievePublished($identifier);
    $data = $this->jsonStringToRootedJsonData($schema_id, $json_string);
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
   * @param \RootedData\RootedJsonData $data
   *   The Json input.
   *
   * @return \RootedData\RootedJsonData
   *   The Json, modified by each applicable discovered data modifier plugins.
   */
  private function modifyData(string $schema_id, RootedJsonData $data): RootedJsonData {
    foreach ($this->plugins as $plugin) {
      // TODO: make sure plugins can work with RootedJsonData.
      if ($plugin->requiresModification($schema_id, $data)) {
        $data = $plugin->modify($schema_id, $data);
      }
    }

    return $data;
  }

  /**
   * Converts Json string into RootedJsonData object.
   *
   * @param \Drupal\metastore\string $schema_id
   *   The {schema_id} slug from the HTTP request.
   * @param \Drupal\metastore\string $json_string
   *   Json string.
   *
   * @return \RootedData\RootedJsonData
   *   RootedJsonData object.
   *
   * @throws \JsonPath\InvalidJsonException
   */
  public function jsonStringToRootedJsonData(string $schema_id, string $json_string): RootedJsonData {
    $schema = $this->schemaRetriever->retrieve($schema_id);
    return new RootedJsonData($json_string, $schema);
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
    $json_string = $this->getStorage($schema_id)->retrieve($identifier);
    $data = $this->jsonStringToRootedJsonData($schema_id, $json_string);

    /* @todo decouple from POD. */
    $resources = $data->{"$.distribution"};;

    return $resources;
  }

  /**
   * Implements POST method.
   *
   * @param string $schema_id
   *   The {schema_id} slug from the HTTP request.
   * @param \RootedData\RootedJsonData $data
   *   Json payload.
   *
   * @return string
   *   The identifier.
   */
  public function post($schema_id, RootedJsonData $data): string {
    $identifier = NULL;

    // If resource already exists, return HTTP 409 Conflict and existing uri.
    if (!empty($data->{'$.identifier'})) {
      $identifier = $data->{'$.identifier'};
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
   * @param \RootedData\RootedJsonData $data
   *   Json payload.
   *
   * @return array
   *   ["identifier" => string, "new" => boolean].
   */
  public function put($schema_id, $identifier, RootedJsonData $data): array {
    if (!empty($data->{'$.identifier'}) && $data->{'$.identifier'} != $identifier) {
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
   * @param \RootedData\RootedJsonData $data
   *   Json payload.
   *
   * @return array
   *   ["identifier" => string, "new" => boolean].
   */
  private function proceedWithPut($schema_id, $identifier, RootedJsonData $data): array {
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
   * @param \RootedData\RootedJsonData $data
   *   Json payload.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The json response.
   */
  public function patch($schema_id, $identifier, RootedJsonData $data) {
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
   * @param \RootedData\RootedJsonData $metadata
   *   The new data being compared to the existing data.
   *
   * @return bool
   *   TRUE if the metadata is equivalent, false otherwise.
   */
  private function objectIsEquivalent(string $schema_id, string $identifier, RootedJsonData $metadata) {
    $existingMetadata = $this->getStorage($schema_id)->retrieve($identifier);
    $existing = json_decode($existingMetadata);
    $existing = self::removeReferences($existing);
    // TODO: find out if we can use RootedJsonData instead.
    $new = json_decode($metadata);
    return $new == $existing;
  }

  /**
   * Private.
   */
  public static function removeReferences($object, $prefix = "%") {
    // TODO: consider replacing with RootedJsonData.
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

}
