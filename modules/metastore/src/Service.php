<?php

namespace Drupal\metastore;

use Contracts\StorerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\common\EventDispatcherTrait;
use Drupal\metastore\Exception\CannotChangeUuidException;
use Drupal\metastore\Exception\ExistingObjectException;
use Drupal\metastore\Exception\MissingObjectException;
use Drupal\metastore\Exception\UnmodifiedObjectException;
use Drupal\metastore\Storage\DataFactory;
use JsonSchema\Exception\ValidationException;
use OpisErrorPresenter\Implementation\MessageFormatterFactory;
use OpisErrorPresenter\Implementation\PresentedValidationErrorFactory;
use OpisErrorPresenter\Implementation\ValidationErrorPresenter;
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
      $container->get('dkan.metastore.schema_retriever'),
      $container->get('dkan.metastore.storage')
    );
  }

  /**
   * Constructor.
   */
  public function __construct(SchemaRetriever $schemaRetriever, DataFactory $factory) {
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
   * @return \Drupal\metastore\Storage\StorerInterface
   *   Entity storage.
   */
  private function getStorage(string $schema_id): StorerInterface {
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
    $data = $this->getStorage($schema_id)->retrievePublished($identifier);
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
    // TODO: abandon the method and use RootedJsonData instead on JSON string.
    $this->validateJson($schema_id, $data);

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

        $new = json_encode($patched);
        // TODO: abandon the method and use RootedJsonData instead on JSON string.
        $this->validateJson($schema_id, $new);

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
   * Get validation result.
   *
   * @param \Drupal\metastore\string $schema_id
   *   The {schema_id} slug from the HTTP request.
   * @param \Drupal\metastore\string $json_data
   *   Json payload.
   *
   * @return array
   *   The validation result.
   *
   * @throws \Exception
   */
  public function getValidationInfo(string $schema_id, string $json_data): array {
    $schema = $this->schemaRetriever->retrieve($schema_id);
    $result = RootedJsonData::validate($json_data, $schema);
    $presenter = new ValidationErrorPresenter(
      new PresentedValidationErrorFactory(
        new MessageFormatterFactory()
      )
    );
    $presented = $presenter->present(...$result->getErrors());
    return ['valid' => empty($presented), 'errors' => $presented];
  }

  /**
   * Validate JSON.
   *
   * @param \Drupal\metastore\string $schema_id
   *   The {schema_id} slug from the HTTP request.
   * @param \Drupal\metastore\string $json_data
   *   Json payload.
   *
   * @return bool
   *   Valid or not.
   *
   * @throws \Exception
   */
  public function validateJson(string $schema_id, string $json_data): bool {
    $validation_info = $this->getValidationInfo($schema_id, $json_data);
    if (!$validation_info['valid']) {
      throw new ValidationException(json_encode((object) $validation_info['errors']));
    }
    return TRUE;
  }

}
