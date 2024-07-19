<?php

namespace Drupal\datastore\Service\ResourceProcessor;

/**
 * Thrown when the DictionaryEnforcer can't find a dictionary for a resource.
 *
 * @see Drupal\datastore\Service\ResourceProcessor\DictionaryEnforcer::getDataDictionaryForResource()
 */
class ResourceDoesNotHaveDictionary extends \RuntimeException {

  private string $resourceId;

  private int $resourceVersion;

  public function __construct(string $resource_id, int $resource_version) {
    $this->resourceId = $resource_id;
    $this->resourceVersion = $resource_version;
    parent::__construct(sprintf('No data-dictionary found for resource with id "%s" and version "%s".', $resource_id, $resource_version));
  }

  public function getResourceId(): string {
    return $this->resourceId;
  }

  public function getResourceVersion(): int {
    return $this->resourceVersion;
  }

}
