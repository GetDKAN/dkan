<?php

namespace Drupal\metastore;

use Drupal\common\Resource;
use Drupal\common\Storage\Query;
use Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Drupal\metastore\Events\Registration;
use Drupal\metastore\Exception\AlreadyRegistered;
use Drupal\metastore\Storage\ResourceMapperDatabaseTable;

/**
 * ResourceMapper.
 */
class ResourceMapper {

  const EVENT_REGISTRATION = 'dkan_metastore_resource_mapper_registration';

  const DEREFERENCE_NO = 0;
  const DEREFERENCE_YES = 1;

  /**
   * The resource mapper database table.
   * @var \Drupal\metastore\Storage\ResourceMapperDatabaseTable;
   */
  private $store;
  private $eventDispatcher;

  /**
   * Constructor.
   */
  public function __construct(ResourceMapperDatabaseTable $store, ContainerAwareEventDispatcher $eventDispatcher) {
    $this->store = $store;
    $this->eventDispatcher = $eventDispatcher;
  }

  /**
   * Register a new url for mapping.
   *
   * @todo the Resource class currently lives in datastore, we should move it
   * to a more neutral place.
   */
  public function register(Resource $resource) : bool {
    $this->filePathExists($resource->getFilePath());
    $this->store->store(json_encode($resource));
    $this->eventDispatcher->dispatch(self::EVENT_REGISTRATION, new Registration($resource));

    return TRUE;
  }

  /**
   * Register new perspective.
   */
  public function registerNewPerspective(Resource $resource) {
    $identifier = $resource->getIdentifier();
    $version = $resource->getVersion();
    $perspective = $resource->getPerspective();
    if ($this->exists($identifier, Resource::DEFAULT_SOURCE_PERSPECTIVE, $version)) {
      if (!$this->exists($identifier, $perspective, $version)) {
        $this->store->store(json_encode($resource));
        $this->eventDispatcher->dispatch(self::EVENT_REGISTRATION, new Registration($resource));
      }
      else {
        throw new AlreadyRegistered("A resource with identifier {$identifier} and perspective {$perspective} already exists.");
      }
    }
    else {
      throw new \Exception("A resource with identifier {$identifier} was not found.");
    }
  }

  /**
   * Register new version.
   */
  public function registerNewVersion(Resource $resource) {
    $this->validateNewVersion($resource);
    $this->store->store(json_encode($resource));
    $this->eventDispatcher->dispatch(self::EVENT_REGISTRATION,
      new Registration($resource));
  }

  /**
   * Private.
   */
  private function validateNewVersion(Resource $resource) {
    if ($resource->getPerspective() !== Resource::DEFAULT_SOURCE_PERSPECTIVE) {
      throw new \Exception("Only versions of source resources are allowed.");
    }

    $identifier = $resource->getIdentifier();
    if (!$this->exists($identifier, Resource::DEFAULT_SOURCE_PERSPECTIVE)) {
      throw new \Exception(
        "A resource with identifier {$identifier} was not found.");
    }

    $version = $resource->getVersion();
    if ($this->exists($identifier, Resource::DEFAULT_SOURCE_PERSPECTIVE, $version)) {
      throw new AlreadyRegistered(
        "A resource with identifier {$identifier} and version {$version} already exists.");
    }
  }

  /**
   * Retrieve.
   */
  public function get(string $identifier, $perspective = Resource::DEFAULT_SOURCE_PERSPECTIVE, $version = NULL): ?Resource {
    $data = $this->getFull($identifier, $perspective, $version);
    return ($data != FALSE) ? Resource::hydrate(json_encode($data)) : NULL;
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
   *
   * @todo When a resource is removed should we remove all connected resources?
   */
  public function remove(string $identifier) {
    if ($this->exists($identifier, 'source')) {
      $resource = $this->getLatestRevision($identifier, 'source');

      if (isset($resource) && $resource instanceof Resource) {
        $this->store->removeByProperty($resource->getIdentifier(), 'identifier');
      }
    }
  }

  /**
   * Private.
   *
   * @return mixed
   *   object || False
   */
  private function getLatestRevision($identifier, $perspective) : ?Resource {
    $query = $this->getCommonQuery($identifier, $perspective);
    $query->sortByDescending('version');
    $items = $this->store->query($query);
    $latest = reset($items);
    return !empty($latest) ? Resource::hydrate(json_encode($latest)) : NULL;
  }

  /**
   * Private.
   *
   * @return mixed
   *   object || False
   */
  private function getRevision($identifier, $perspective, $version) {
    $query = $this->getCommonQuery($identifier, $perspective);
    $query->conditionByIsEqualTo('version', $version);
    $items = $this->store->query($query);
    return reset($items);
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
    ];
    $query->conditionByIsEqualTo('identifier', $identifier);
    $query->conditionByIsEqualTo('perspective', $perspective);
    $query->limitTo(1);
    return $query;
  }

  /**
   * Check if a file path exists.
   *
   * @param string $filePath
   *   The path to check.
   *
   * @return bool
   *   FALSE of the path does not exist.
   *
   * @throws \Exception
   *   An exception is thrown if the file exists with json info about the
   *   existing resource.
   */
  public function filePathExists($filePath) {
    $query = new Query();
    $query->conditionByIsEqualTo('filePath', $filePath);
    $results = $this->store->query($query);
    if (!empty($results)) {
      throw new AlreadyRegistered(json_encode($results));
    }
    return FALSE;
  }

  /**
   * Private.
   */
  private function exists($identifier, $perspective, $version = NULL) {
    $item = $this->get($identifier, $perspective, $version);
    return isset($item) ? TRUE : FALSE;
  }

}
