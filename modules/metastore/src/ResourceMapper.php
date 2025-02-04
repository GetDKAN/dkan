<?php

namespace Drupal\metastore;

use Drupal\common\DataResource;
use Drupal\common\Storage\DatabaseTableInterface;
use Drupal\common\EventDispatcherTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\datastore\Service\ResourceLocalizer;
use Drupal\metastore\Exception\AlreadyRegistered;

/**
 * Map resource URLs to local files.
 */
class ResourceMapper {

  use EventDispatcherTrait;

  const EVENT_REGISTRATION = 'dkan_metastore_resource_mapper_registration';

  const EVENT_RESOURCE_MAPPER_PRE_REMOVE_SOURCE = 'dkan_metastore_pre_remove_source';

  const DEREFERENCE_NO = 0;

  const DEREFERENCE_YES = 1;

  /**
   * Database storage service.
   *
   * @var \Drupal\common\Storage\DatabaseTableInterface
   *
   * @todo Deprecate/remove this form of storage.
   */
  private $store;

  /**
   * Entity type manager service.
   */
  private EntityTypeManagerInterface $entityTypeManager;

  /**
   * Entity storage service.
   *
   * The data used by the ResourceMapper is stored in resource_mapping entities.
   *
   * @see \Drupal\metastore\Entity\ResourceMapping
   */
  private EntityStorageInterface $mappingEntityStorage;

  /**
   * Constructor.
   */
  public function __construct(
    DatabaseTableInterface $store,
    EntityTypeManagerInterface $entityTypeManager,
  ) {
    $this->store = $store;
    $this->entityTypeManager = $entityTypeManager;
    $this->mappingEntityStorage = $this->entityTypeManager
      ->getStorage('resource_mapping');
  }

  /**
   * Helper method to retrieve the static value for a resource's display.
   *
   * @return string
   *   A resource perspective.
   */
  public static function newRevision() {
    return \drupal_static('metastore_resource_mapper_new_revision', 0);
  }

  /**
   * Register a new url for mapping.
   */
  public function register(DataResource $resource): bool {
    // Call filePathExists() so it throws an exception if the path is a
    // duplicate.
    $this->filePathExists($resource->getFilePath());
    $this->storeResourceToMapping($resource);
    $this->dispatchEvent(self::EVENT_REGISTRATION, $resource);

    return TRUE;
  }

  /**
   * Register new resource perspective.
   *
   * @param \Drupal\common\DataResource $resource
   *   Resource for which to register new perspective.
   */
  public function registerNewPerspective(DataResource $resource): void {
    $identifier = $resource->getIdentifier();
    $version = $resource->getVersion();
    // Ensure a source perspective already exists for the resource.
    if (!$this->exists($identifier, DataResource::DEFAULT_SOURCE_PERSPECTIVE, $version)) {
      throw new \Exception("A resource with identifier {$identifier} was not found.");
    }

    $perspective = $resource->getPerspective();
    // Ensure the current perspective does not already exist for the resource.
    if ($this->exists($identifier, $perspective, $version)) {
      throw new AlreadyRegistered("A resource with identifier {$identifier} and perspective {$perspective} already exists.");
    }

    // If the given resource has a local file, generate a checksum for the
    // file before storing the resource.
    if ($perspective == ResourceLocalizer::LOCAL_FILE_PERSPECTIVE) {
      $resource->generateChecksum();
    }

    // Record resource in mapper table and dispatch an event for the
    // resource's registration.
    $this->storeResourceToMapping($resource);
    $this->dispatchEvent(self::EVENT_REGISTRATION, $resource);
  }

  /**
   * Register new version.
   */
  public function registerNewVersion(DataResource $resource) {
    $this->validateNewVersion($resource);
    $this->storeResourceToMapping($resource);
    $this->dispatchEvent(self::EVENT_REGISTRATION, $resource);
  }

  /**
   * Store the DataResource to a mapping entity.
   *
   * @param \Drupal\common\DataResource $resource
   *   The data resource.
   *
   * @return int
   *   Either SAVED_NEW or SAVED_UPDATED, depending on the operation performed.
   */
  protected function storeResourceToMapping(DataResource $resource): int {
    $map = $this->mappingEntityStorage->create([
      'identifier' => $resource->getIdentifier(),
      'version' => $resource->getVersion(),
      'filePath' => $resource->getFilePath(),
      'perspective' => $resource->getPerspective(),
      'mimeType' => $resource->getMimeType(),
      'checksum' => $resource->getChecksum(),
    ]);
    return $map->save();
  }

  /**
   * Private.
   */
  protected function validateNewVersion(DataResource $resource) {
    if ($resource->getPerspective() !== DataResource::DEFAULT_SOURCE_PERSPECTIVE) {
      throw new \Exception('Only versions of source resources are allowed.');
    }

    $identifier = $resource->getIdentifier();
    if (!$this->exists($identifier, DataResource::DEFAULT_SOURCE_PERSPECTIVE)) {
      throw new \Exception(
        "A resource with identifier {$identifier} was not found.");
    }

    $version = $resource->getVersion();
    if ($this->exists($identifier, DataResource::DEFAULT_SOURCE_PERSPECTIVE, $version)) {
      throw new AlreadyRegistered(
        "A resource with identifier {$identifier} and version {$version} already exists.");
    }
  }

  /**
   * Retrieve a data resource.
   *
   * @param string $identifier
   *   Data resource identifier.
   * @param string $perspective
   *   (Optional) Data resource perspective. The source perspective will be used
   *   if not provided.
   * @param null|string $version
   *   (Optional) Data resource version. The newest version will be used if not
   *   provided.
   *
   * @return \Drupal\common\DataResource|null
   *   DataResource for the mapping.
   */
  public function get(
    string $identifier,
    string $perspective = DataResource::DEFAULT_SOURCE_PERSPECTIVE,
    ?string $version = NULL,
  ): ?DataResource {
    $data = $this->getFull($identifier, $perspective, $version);
    if ($data) {
      return DataResource::createFromEntity($data);
    }
    return NULL;
  }

  /**
   * Get a resource mapping entity.
   *
   * @param string $identifier
   *   Resource identifier.
   * @param string $perspective
   *   Resource perspective.
   * @param null|string $version
   *   (Optional) Resource version. If not supplied, the latest revision will be
   *   returned.
   *
   * @return \Drupal\metastore\ResourceMappingInterface|null
   *   Resource mapping.
   */
  private function getFull(string $identifier, string $perspective, ?string $version = NULL): ?ResourceMappingInterface {
    if (!$version) {
      $data = $this->getLatestRevision($identifier, $perspective);
    }
    else {
      $data = $this->getRevision($identifier, $perspective, $version);
    }
    return $data;
  }

  /**
   * Remove mapping entry representing the given resource object.
   *
   * @param \Drupal\common\DataResource $resource
   *   DataResource object to be removed.
   */
  public function remove(DataResource $resource) {
    if ($this->exists($resource->getIdentifier(), $resource->getPerspective(), $resource->getVersion())) {
      $mapping = $this->getRevision(
        $resource->getIdentifier(),
        $resource->getPerspective(),
        $resource->getVersion()
      );
      if ($resource->getPerspective() == DataResource::DEFAULT_SOURCE_PERSPECTIVE) {
        // Dispatch event to initiate removal of the datastore and local file.
        $this->dispatchEvent(self::EVENT_RESOURCE_MAPPER_PRE_REMOVE_SOURCE, $resource);
      }
      // Remove the resource mapper perspective.
      $this->mappingEntityStorage->delete([$mapping]);
    }
  }

  /**
   * Private.
   *
   * @return \Drupal\metastore\ResourceMappingInterface|null
   *   Resource mapping.
   */
  private function getLatestRevision($identifier, $perspective): ?ResourceMappingInterface {
    $map_ids = $this->mappingEntityStorage->getQuery()
      ->condition('identifier', $identifier)
      ->condition('perspective', $perspective)
      ->sort('version', 'DESC')
      ->range(0, 1)
      ->accessCheck(FALSE)
      ->execute();
    if ($map_ids) {
      return $this->mappingEntityStorage->load(reset($map_ids));
    }
    return NULL;
  }

  /**
   * Get the DB record for the mapping, accounting for version.
   *
   * @return \Drupal\metastore\ResourceMappingInterface|null
   *   Resource mapping.
   */
  private function getRevision($identifier, $perspective, $version): ?ResourceMappingInterface {
    $map_ids = $this->mappingEntityStorage->getQuery()
      ->condition('identifier', $identifier)
      ->condition('perspective', $perspective)
      ->condition('version', $version)
      ->range(0, 1)
      ->accessCheck(FALSE)
      ->execute();
    if ($map_ids) {
      return $this->mappingEntityStorage->load(reset($map_ids));
    }
    return NULL;
  }

  /**
   * Check if a file path exists in any record in the mapping DB.
   *
   * @param string $filePath
   *   The path to check.
   *
   * @return bool
   *   FALSE of the path does not exist.
   *
   * @throws \Drupal\metastore\Exception\AlreadyRegistered
   *   An exception is thrown if the file exists with json info about the
   *   existing resource.
   */
  public function filePathExists($filePath) {
    $map_ids = $this->mappingEntityStorage->getQuery()
      ->condition('filePath', $filePath)
      ->accessCheck(FALSE)
      ->execute();
    if ($map_ids) {
      $mappings = $this->mappingEntityStorage->loadMultiple($map_ids);
      throw (new AlreadyRegistered(json_encode($mappings)))
        ->setAlreadyRegistered($mappings);
    }
    return FALSE;
  }

  /**
   * Private.
   */
  private function exists($identifier, $perspective, $version = NULL): bool {
    $item = $this->get($identifier, $perspective, $version);
    return isset($item);
  }

}
