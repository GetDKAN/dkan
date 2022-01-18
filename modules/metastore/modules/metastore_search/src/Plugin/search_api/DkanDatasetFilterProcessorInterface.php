<?php

namespace Drupal\metastore_search\Plugin\search_api;

use Drupal\search_api\Processor\ProcessorInterface;

/**
 * Dataset filter processor interface.
 */
interface DkanDatasetFilterProcessorInterface extends ProcessorInterface {

  /**
   * Determine whether the dataset belonging to the given ID should be included.
   *
   * @param string $dataset_id
   *   DKAN dataset ID.
   *
   * @return bool
   *   Whether the dataset should be included.
   */
  public function isValid(string $dataset_id): bool;

}
