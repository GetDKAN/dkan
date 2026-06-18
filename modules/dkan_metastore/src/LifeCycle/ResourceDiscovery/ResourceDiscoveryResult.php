<?php

namespace Drupal\dkan_metastore\LifeCycle\ResourceDiscovery;

use Drupal\dkan_common\DataResource;

/**
 * Data object to hold the result of the resource discovery process.
 */
class ResourceDiscoveryResult {
  /**
   * An array of discovered resources.
   *
   * @var \Drupal\dkan_common\DataResource[]
   */
  protected array $discoveredResources;

  /**
   * An array of skipped entries.
   *
   * @var \Drupal\dkan_metastore\LifeCycle\ResourceDiscovery\SkippedEntry[]
   */
  protected array $skippedEntries;

  /**
   * ResourceDiscoveryResult constructor.
   *
   * @param string $datasetId
   *   The identifier of the dataset for which resources were discovered.
   */
  public function __construct(public readonly string $datasetId) {
    $this->discoveredResources = [];
    $this->skippedEntries = [];
  }

  /**
   * Add a discovered resource to the result.
   *
   * @param \Drupal\dkan_common\DataResource $resource
   *   The data of the discovered resource.
   */
  public function addDiscoveredResource(DataResource $resource): void {
    $this->discoveredResources[] = $resource;
  }

  /**
   * Add a skipped entry to the result.
   *
   * @param \Drupal\dkan_metastore\LifeCycle\ResourceDiscovery\SkippedEntry $entry
   *   The skipped entry.
   */
  public function addSkippedEntry(SkippedEntry $entry): void {
    $this->skippedEntries[] = $entry;
  }

  /**
   * Get the skipped entries.
   *
   * @return \Drupal\dkan_metastore\LifeCycle\ResourceDiscovery\SkippedEntry[]
   *   The skipped entries.
   */
  public function getSkippedEntries(): array {
    return $this->skippedEntries;
  }

}
