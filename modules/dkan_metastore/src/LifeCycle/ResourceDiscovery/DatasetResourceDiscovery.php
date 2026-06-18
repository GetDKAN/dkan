<?php

namespace Drupal\dkan_metastore\LifeCycle\ResourceDiscovery;

/**
 * Service class for discovering resources from dataset metadata.
 */
class DatasetResourceDiscovery implements ResourceDiscoveryInterface {

  /**
   * {@inheritdoc}
   */
  public function discoverResources(object $metadata): ResourceDiscoveryResult {
    // @todo Implement the logic to discover resources from the metadata object.
    $dataset_id = $metadata->identifier ?? NULL;
    if (!$dataset_id) {
      throw new \InvalidArgumentException('Metadata object must contain an identifier property.');
    }
    $resources = [];
    $result = new ResourceDiscoveryResult($dataset_id);
    foreach ($resources as $resource) {
      $result->addDiscoveredResource($resource);
    }

    return $result;
  }

}
