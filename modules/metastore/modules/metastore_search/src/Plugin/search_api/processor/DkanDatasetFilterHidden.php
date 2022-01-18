<?php

namespace Drupal\metastore_search\Plugin\search_api\processor;

use Drupal\metastore_search\Plugin\search_api\DkanDatasetFilterProcessorBase;

/**
 * Excludes hidden datasets from data indexes.
 *
 * @SearchApiProcessor(
 *   id = "dkan_dataset_filter_hidden",
 *   label = @Translation("DKAN Dataset Filter Hidden"),
 *   description = @Translation("Exclude hidden datasets from being indexed."),
 *   stages = {
 *     "alter_items" = 0,
 *   },
 * )
 */
class DkanDatasetFilterHidden extends DkanDatasetFilterProcessorBase {

  /**
   * {@inheritdoc}
   */
  public function isValid(string $dataset_id): bool {
    return !$this->dataStorage->isHidden($dataset_id);
  }

}
