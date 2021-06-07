<?php

namespace Drupal\metastore;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\common\EventDispatcherTrait;
use Drupal\metastore\Exception\CannotChangeUuidException;
use Drupal\metastore\Exception\ExistingObjectException;
use Drupal\metastore\Exception\MissingObjectException;
use Drupal\metastore\Exception\UnmodifiedObjectException;
use Drupal\metastore\Storage\DataFactory;
use Drupal\metastore\Storage\MetastoreStorageInterface;
use RootedData\RootedJsonData;
use Rs\Json\Merge\Patch;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Service.
 */
class Service implements ContainerInjectionInterface {
  use EventDispatcherTrait;

  const EVENT_DATA_GET = 'dkan_metastore_data_get';
  const EVENT_DATA_GET_ALL = 'dkan_metastore_data_get_all';

  /**
   * Schema retriever.
   *
   * @var \Drupal\metastore\SchemaRetriever
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
   * RootedJsonData wrapper.
   *
   * @var \Drupal\metastore\ValidMetadataFactory
   */
  private $validMetadataFactory;

  /**
   * Inherited.
   *
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new Service(
      $container->get('dkan.metastore.schema_retriever'),
      $container->get('dkan.metastore.storage'),
      $container->get('dkan.metastore.valid_metadata')
    );
  }

  /**
   * Constructor.
   */
  public function __construct(SchemaRetriever $schemaRetriever, DataFactory $factory, ValidMetadataFactory $validMetadataFactory) {
    $this->schemaRetriever = $schemaRetriever;
    $this->storageFactory = $factory;
    $this->validMetadataFactory = $validMetadataFactory;
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
    $jsonStringsArray = $this->getStorage($schema_id)->retrieveAll();

    $objects = array_map(
      function ($jsonString) use ($schema_id) {
        $data = $this->validMetadataFactory->get($schema_id, $jsonString);
        try {
          return $this->dispatchEvent(self::EVENT_DATA_GET, $data, function ($data) {
            return $data instanceof RootedJsonData;
          });
        }
        catch (\Exception $e) {
          return new RootedJsonData(json_encode(["message" => $e->getMessage()]));
        }
      },
      $jsonStringsArray
    );

    return $this->dispatchEvent(self::EVENT_DATA_GET_ALL, $objects, function ($data) {
      if (!is_array($data)) {
        return FALSE;
      }
      if (count($data) == 0) {
        return TRUE;
      }
      return $data[0] instanceof RootedJsonData;
    });

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
    $data = $this->validMetadataFactory->get($schema_id, $json_string);

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
    $json_string = $this->getStorage($schema_id)->retrieve($identifier);
    $data = $this->validMetadataFactory->get($schema_id, $json_string);

    /* @todo decouple from POD. */
    $resources = $data->{"$.distribution"};

    return $resources;
  }

  /**
   * Get ValidMetadataFactory.
   *
   * @return \Drupal\metastore\ValidMetadataFactory
   *   rootedJsonDataWrapper.
   */
  public function getValidMetadataFactory() {
    return $this->validMetadataFactory;
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
   * @return bool
   *   True if the dataset is successfully published, false otherwise.
   */
  public function publish(string $schema_id, string $identifier): bool {
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
   * @param mixed $json_data
   *   Json payload.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The json response.
   */
  public function patch($schema_id, $identifier, $json_data) {
    $storage = $this->getStorage($schema_id);
    if ($this->objectExists($schema_id, $identifier)) {

      $json_data_original = $storage->retrieve($identifier);
      if ($json_data_original) {
        $patched = (new Patch())->apply(
          json_decode($json_data_original),
          json_decode($json_data)
        );

        $new = $this->validMetadataFactory->get($schema_id, json_encode($patched));
        $storage->store($new, "{$identifier}");
        return $identifier;
      }

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
    $existing = $this->getValidMetadataFactory()->get($schema_id, $existingMetadata);
    $existing = self::removeReferences($existing);
    return $metadata->get('$') == $existing->get('$');
  }

  /**
   * Private.
   */
  public static function removeReferences(RootedJsonData $object, $prefix = "%"): RootedJsonData {
    $array = $object->get('$');

    foreach ($array as $property => $value) {
      if (substr_count($property, $prefix) > 0) {
        unset($array[$property]);
      }
    }

    if (isset($array['distribution'][0]['%Ref:downloadURL'])) {
      unset($array['distribution'][0]['%Ref:downloadURL']);
    }

    $object->set('$', $array);
    return $object;
  }

}
