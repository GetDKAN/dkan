<?php

namespace Drupal\metastore;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\common\EventDispatcherTrait;
use Drupal\common\LoggerTrait;
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
 * The metastore service.
 */
class MetastoreService implements ContainerInjectionInterface {
  use EventDispatcherTrait;
  use LoggerTrait;

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
    return new MetastoreService(
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
  protected function getStorage(string $schema_id): MetastoreStorageInterface {
    if (!isset($this->storages[$schema_id])) {
      $this->storages[$schema_id] = $this->storageFactory->getInstance($schema_id);
    }
    return $this->storages[$schema_id];
  }

  /**
   * Count objects of the given schema ID.
   *
   * @param string $schema_id
   *   The schema ID to be counted.
   * @param bool $unpublished
   *   Whether to include unpublished items.
   *
   * @return int
   *   Object count.
   */
  public function count(string $schema_id, bool $unpublished = FALSE): int {
    return $this->getStorage($schema_id)->count($unpublished);
  }

  /**
   * Get range of object UUIDs of the given schema ID.
   *
   * If no start or length is specified, all dataset UUIDs will be returned.
   *
   * @param string $schema_id
   *   The schema ID to be counted.
   * @param int|null $start
   *   Schema object offset or null for all.
   * @param int|null $length
   *   Number of objects to fetch or null for all.
   * @param bool $unpublished
   *   Whether to include unpublished items.
   *
   * @return string[]
   *   Range of object UUIDs of the given schema_id.
   */
  public function getIdentifiers(string $schema_id, ?int $start = NULL, ?int $length = NULL, $unpublished = FALSE): array {
    return $this->getStorage($schema_id)->retrieveIds($start, $length, $unpublished);
  }

  /**
   * Get all items, with optional pagination and status filters.
   *
   * @param string $schema_id
   *   Schema ID.
   * @param int|null $start
   *   Start offset. Null for no range.
   * @param int|null $length
   *   Number of items to retrieve.
   * @param bool $unpublished
   *   Whether to include unpublished items. Should default to false.
   *
   * @return array
   *   Array of RootedJsonData objects.
   */
  public function getAll(string $schema_id, ?int $start = NULL, ?int $length = NULL, $unpublished = FALSE):array {
    $jsonStringsArray = $this->getStorage($schema_id)->retrieveAll($start, $length, $unpublished);
    $objects = array_filter($this->jsonStringsArrayToObjects($jsonStringsArray, $schema_id));

    return $this->dispatchEvent(self::EVENT_DATA_GET_ALL, $objects, function ($data) {
      if (!is_array($data)) {
        return FALSE;
      }
      if (count($data) == 0) {
        return TRUE;
      }
      return reset($data) instanceof RootedJsonData;
    });

  }

  /**
   * Create array of RootedJsonData objects from array of strings.
   *
   * @param array $jsonStringsArray
   *   Array of JSON strings.
   * @param string $schema_id
   *   Schema ID.
   *
   * @return array
   *   Array of objects.
   *
   * @todo Exception should not be caught; let controller handle it.
   */
  private function jsonStringsArrayToObjects(array $jsonStringsArray, string $schema_id) {
    return array_map(
      function ($jsonString) use ($schema_id) {
        try {
          $data = $this->validMetadataFactory->get($jsonString, $schema_id);
          return $this->dispatchEvent(self::EVENT_DATA_GET, $data, function ($data) {
            return $data instanceof RootedJsonData;
          });
        }
        catch (\Exception $e) {
          $this->log('metastore', 'A JSON string failed validation.',
            ['@schema_id' => $schema_id, '@json' => $jsonString]
          );
          return NULL;
        }
      }, $jsonStringsArray);
  }

  /**
   * Determine whether the given metastore item is published.
   *
   * @param string $schema_id
   *   The metastore schema in question.
   * @param string $identifier
   *   The ID of the metastore item in question.
   *
   * @return bool
   *   Whether the given metastore item is published.
   */
  public function isPublished(string $schema_id, string $identifier): bool {
    return $this->getStorage($schema_id)->isPublished($identifier);
  }

  /**
   * Implements GET method.
   *
   * @param string $schema_id
   *   The {schema_id} slug from the HTTP request.
   * @param string $identifier
   *   Identifier.
   * @param bool $published
   *   If true, retrieve only published revision.
   *
   * @return \RootedData\RootedJsonData
   *   The json data.
   */
  public function get(string $schema_id, string $identifier, bool $published = TRUE): RootedJsonData {
    $json_string = $this->getStorage($schema_id)->retrieve($identifier, $published);
    $data = $this->validMetadataFactory->get($json_string, $schema_id);

    $data = $this->dispatchEvent(self::EVENT_DATA_GET, $data);
    return $data;
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
   * Publish an item's update by making its latest revision its default one.
   *
   * @param string $schema_id
   *   The {schema_id} slug from the HTTP request.
   * @param string $identifier
   *   Identifier.
   *
   * @return bool
   *   True if the dataset is successfully archived, false otherwise.
   */
  public function archive(string $schema_id, string $identifier): bool {
    if ($this->objectExists($schema_id, $identifier)) {
      return $this->getStorage($schema_id)->archive($identifier);
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

        $new = $this->validMetadataFactory->get(json_encode($patched), $schema_id);
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
    $catalog->dataset = array_map(function ($object) {
      $modified_object = $this->removeReferences($object);
      return (object) $modified_object->get('$');
    }, $this->getAll('dataset'));

    return $catalog;
  }

  /**
   * Insert full references with identifiers into metadata fields.
   *
   * @param \RootedData\RootedJsonData $object
   *   The valid metastore item.
   *
   * @return \RootedData\RootedJsonData
   *   Swapped reference object. RootedJsonData for consistency, but will have
   *   no defined schema (swapping will break validation).
   *
   * @throws \Drupal\metastore\Exception\InvalidJsonException
   */
  public function swapReferences(RootedJsonData $object): RootedJsonData {
    $no_schema_object = $this->getValidMetadataFactory()->get("$object", NULL);
    foreach ($no_schema_object->get('$') as $property => $value) {
      if (substr_count($property, "%Ref:") > 0) {
        $no_schema_object = $this->swapReference($property, $value, $no_schema_object);
      }
    }

    return self::removeReferences($no_schema_object, "%Ref");
  }

  /**
   * Private.
   */
  private function swapReference($property, $value, RootedJsonData $object): RootedJsonData {
    $original = str_replace("%Ref:", "", $property);
    if ($object->__isset("$.{$original}")) {
      $object->set("$.{$original}", $value);
    }
    return $object;
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
    $existing = $this->getValidMetadataFactory()->get($existingMetadata, $schema_id);
    $existing = self::removeReferences($existing);
    return $metadata->get('$') == $existing->get('$');
  }

  /**
   * Remove references from metadata JSON.
   *
   * @param \RootedData\RootedJsonData $object
   *   Metadata JSON object.
   * @param string $prefix
   *   Property prefix.
   *
   * @return \RootedData\RootedJsonData
   *   The metadata without any reference artifacts.
   *
   * @todo Probably remove the prefix param and just always use "%Ref".
   */
  public static function removeReferences(RootedJsonData $object, $prefix = "%"): RootedJsonData {
    $array = $object->get('$');

    foreach ($array as $property => $value) {
      if (substr_count($property, $prefix) > 0) {
        unset($array[$property]);
      }
    }

    if (!empty($array['distribution'])) {
      $array['distribution'] = array_map(function ($dist) {
        unset($dist['%Ref:downloadURL']);
        return $dist;
      }, $array['distribution']);
    }

    $object->set('$', $array);
    return $object;
  }

  /**
   * Get the md5 hash for a metadata item.
   *
   * @param \RootedData\RootedJsonData|object|string $data
   *   Metadata. Can be a RootedJsonData object, a stdObject or JSON string.
   *
   * @return string
   *   An md5 hash of the normalized metadata.
   *
   * @todo This should probably be somewhere else.
   */
  public static function metadataHash($data) {
    if ($data instanceof RootedJsonData) {
      $normalizedData = $data;
      self::removeReferences($normalizedData);
    }
    elseif (is_object($data)) {
      $normalizedData = new RootedJsonData(json_encode($data));
      self::removeReferences($normalizedData);
    }
    elseif (is_string($data)) {
      $normalizedData = $data;
    }
    else {
      throw new \InvalidArgumentException("Invalid metadata argument.");
    }

    return md5((string) $normalizedData);
  }

}
