<?php

namespace Drupal\metastore;

use Drupal\common\DataResource;
use Drupal\common\Storage\DatabaseTableInterface;
use Drupal\common\Storage\Query;
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
   */
  private $store;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private EntityTypeManagerInterface $entityTypeManager;

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  private EntityStorageInterface $entityStorage;

  /**
   * Constructor.
   */
  public function __construct(
    DatabaseTableInterface $store,
    EntityTypeManagerInterface $entityTypeManager
  ) {
    $this->store = $store;
    $this->entityTypeManager = $entityTypeManager;
    $this->entityStorage = $this->entityTypeManager
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
      throw new \Exception('A resource with identifier ' . $identifier . ' was not found.');
    }

    $perspective = $resource->getPerspective();
    // Ensure the current perspective does not already exist for the resource.
    if ($this->exists($identifier, $perspective, $version)) {
      throw new AlreadyRegistered('A resource with identifier ' . $identifier . ' and perspective ' . $perspective . ' already exists.');
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
   *
   * @todo This illustrates why DataResource should really be the entity.
   *   Fix this in 3.x.
   */
  protected function storeResourceToMapping(DataResource $resource): int {
    $map = $this->entityStorage->create([
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
        'A resource with identifier ' . $identifier . ' was not found.');
    }

    $version = $resource->getVersion();
    if ($this->exists($identifier, DataResource::DEFAULT_SOURCE_PERSPECTIVE, $version)) {
      throw new AlreadyRegistered(
        'A resource with identifier ' . $identifier . ' and version ' . $version . ' already exists.');
    }
  }

  /**
   * Retrieve.
   */
  public function get(string $identifier, $perspective = DataResource::DEFAULT_SOURCE_PERSPECTIVE, $version = NULL): ?DataResource {
    $data = $this->getFull($identifier, $perspective, $version);
    if ($data !== FALSE) {
      return DataResource::createFromRecord($data);
    }
    return NULL;
  }

  /**
   * Private.
   */
  private function getFull(string $identifier, $perspective, $version) {
    if (!$version) {
      $data = $this->getLatestRevision($identifier, $perspective);
    }
    else {
      $data = $this->getRevision($identifier, $perspective, $version);
    }
    return $data;
  }

  /**
   * Remove.
   */
  public function remove(DataResource $resource) {
    if ($this->exists($resource->getIdentifier(), $resource->getPerspective(), $resource->getVersion())) {
      $mapping = $this->getRevision($resource->getIdentifier(), $resource->getPerspective(), $resource->getVersion());
      if ($resource->getPerspective() == DataResource::DEFAULT_SOURCE_PERSPECTIVE) {
        // Dispatch event to initiate removal of the datastore and local file.
        $this->dispatchEvent(self::EVENT_RESOURCE_MAPPER_PRE_REMOVE_SOURCE, $resource);
      }
      // Remove the resource mapper perspective.
      $this->entityStorage->delete([$mapping]);
    }
  }

  /**
   * Private.
   *
   * @return mixed
   *   object || False
   */
  private function getLatestRevision($identifier, $perspective) {
    $map_ids = $this->entityStorage->getQuery()
      ->condition('identifier', $identifier)
      ->condition('perspective', $perspective)
      ->sort('version', 'DESC')
      ->execute();
    if ($map_ids) {
      return $this->entityStorage->load(reset($map_ids));
    }
    return NULL;
  }

  /**
   * Private.
   *
   * @return mixed
   *   object || False
   */
  private function getRevision($identifier, $perspective, $version) {
    $map_ids = $this->entityStorage->getQuery()
      ->condition('identifier', $identifier)
      ->condition('perspective', $perspective)
      ->condition('version', $version)
      ->execute();
    if ($map_ids) {
      return $this->entityStorage->load(reset($map_ids));
    }
    return NULL;
  }

  /**
   * Private.
   */
  private function getCommonQuery($identifier, $perspective) {
    $query = new Query();
    $query->properties = [
      'identifier',
      'version',
      'perspective',
      'filePath',
      'mimeType',
      'id',
    ];
    $query->conditionByIsEqualTo('identifier', $identifier);
    $query->conditionByIsEqualTo('perspective', $perspective);
    $query->limitTo(1);
    return $query;
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
    $map_ids = $this->entityStorage->getQuery()
      ->condition('filePath', $filePath)
      ->execute();
    if (!empty($map_ids)) {
      $maps = $this->entityStorage->loadMultiple($map_ids);
      throw new AlreadyRegistered(json_encode($maps));
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
