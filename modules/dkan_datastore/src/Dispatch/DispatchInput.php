<?php

namespace Drupal\dkan_datastore\Dispatch;

/**
 * Data object to hold the result of the dispatch process.
 */
class DispatchInput {

  /**
   * Constructor.
   *
   * @param string $datasetId
   *   The identifier of the dataset or other metadata item to be dispatched.
   * @param object|array $datasetMetadata
   *   The full metadata associated with the dataset.
   * @param bool $deferred
   *   Indicates whether the dispatch should be deferred. Defaults to TRUE.
   */
  public function __construct(
    public readonly string $datasetId,
    public readonly object|array $datasetMetadata,
    public readonly bool $deferred = TRUE,
  ) {}

}
