<?php

namespace Drupal\dkan_datastore\Dispatch;

/**
 * Datastore dispatcher service.
 */
class Dispatcher implements DispatcherInterface {

  /**
   * {@inheritdoc}
   */
  public function dispatch(DispatchInput $input): DispatchResult {
    // Placeholder logic.
    return new DispatchResult(
      datasetId: $input->datasetId,
      processedCount: 0,
      skippedCount: 0,
      items: [],
    );
  }

}
