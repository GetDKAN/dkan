<?php

namespace Drupal\dkan_metastore\LifeCycle\ResourceDiscovery;

/**
 * Skipped Entry; a data object for use in resource discovery.
 *
 * Represents a candidate resource entry that was skipped during the discovery
 * process, along with the reason for skipping it.
 */
class SkippedEntry {

  /**
   * Constructor.
   *
   * @param string $filePath
   *   The URL or filepath raw input value of the entry that was skipped.
   * @param string $mimeType
   *   The raw input value for MIME type of the entry that was skipped.
   * @param \Drupal\dkan_metastore\LifeCycle\ResourceDiscovery\SkippedEntryReasons $reason
   *   The reason why the entry was skipped.
   */
  public function __construct(
    public readonly string $filePath,
    public readonly string $mimeType,
    public readonly SkippedEntryReasons $reason,
  ) {}

}
