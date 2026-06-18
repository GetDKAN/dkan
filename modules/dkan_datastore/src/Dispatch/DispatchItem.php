<?php

namespace Drupal\dkan_datastore\Dispatch;

/**
 * Data object to hold the result of the dispatch process.
 */
class DispatchItem {

  /**
   * Constructor.
   *
   * @param string $url
   *   The URL of the resource that was dispatched.
   * @param string $resourceId
   *   The identifier of the resource that was dispatched.
   * @param \Drupal\dkan_datastore\Dispatch\DispatchStatuses $status
   *   The status of the dispatch process for this item.
   * @param string|null $message
   *   An optional message providing additional information about the result.
   */
  public function __construct(
    public readonly string $url,
    public readonly string $resourceId,
    public readonly DispatchStatuses $status,
    public readonly ?string $message = NULL,
  ) {}

}
