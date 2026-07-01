<?php

namespace Drupal\dkan_metastore\Reference\ResourceDiscovery;

/**
 * Interface for resource discovery classes.
 */
interface ResourceDiscoveryInterface {

  /**
   * Discover resources from a given metadata object.
   *
   * @param object $metadata
   *   The json_decoded metadata object from which to discover resources.
   *
   * @return \Drupal\dkan_metastore\Reference\ResourceDiscovery\ResourceDiscoveryResult
   *   The result of the resource discovery process.
   */
  public function discoverResources(object $metadata): ResourceDiscoveryResult;

}
