<?php

namespace Drupal\dkan_datastore\Dispatch;

/**
 * Data object to hold the result of the dispatch process.
 */
class DispatchResult {

  /**
   * Constructor.
   *
   * @param string $datasetId
   *   The identifier of the dataset that was dispatched.
   * @param int $processedCount
   *   The number of items that were successfully processed.
   * @param int $skippedCount
   *   The number of items that were skipped during the dispatch.
   * @param \Drupal\dkan_datastore\Dispatch\DatastoreDispatchItem[] $items
   *   An array of dispatch items representing the results of the dispatch.
   */
  public function __construct(
    public readonly string $datasetId,
    public readonly int $processedCount,
    public readonly int $skippedCount,
    public readonly array $items,
  ) {}

}
