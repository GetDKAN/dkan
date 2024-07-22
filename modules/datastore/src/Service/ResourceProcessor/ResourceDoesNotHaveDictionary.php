<?php

namespace Drupal\datastore\Service\ResourceProcessor;

/**
 * Thrown when a resource does not have an associated data dictionary.
 *
 * @see Drupal\datastore\Service\ResourceProcessor\DictionaryEnforcer::getDataDictionaryForResource()
 */
class ResourceDoesNotHaveDictionary extends \RuntimeException {

  /**
   * The resource ID.
   *
   * @var string
   */
  private string $resourceId;

  /**
   * Constructor.
   *
   * @param string $resource_id
   *   Resource ID.
   * @param int $resource_version
   *   Resource version.
   */
  public function __construct(string $resource_id, int $resource_version) {
    $this->resourceId = $resource_id;
    parent::__construct(sprintf('No data-dictionary found for resource with id "%s" and version "%s".', $resource_id, $resource_version));
  }

  /**
   * Get the resource ID.
   *
   * @return string
   */
  public function getResourceId(): string {
    return $this->resourceId;
  }

}
