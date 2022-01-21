<?php

namespace Drupal\metastore_search\Plugin\search_api\processor;

use Drupal\metastore_search\Plugin\search_api\DkanDatasetFilterProcessorBase;

/**
 * Excludes unpublished datasets from data indexes.
 *
 * @SearchApiProcessor(
 *   id = "dkan_dataset_filter_unpublished",
 *   label = @Translation("DKAN Dataset Filter Unpublished"),
 *   description = @Translation("Exclude unpublished datasets from being indexed."),
 *   stages = {
 *     "alter_items" = 0,
 *   },
 * )
 */
class DkanDatasetFilterUnpublished extends DkanDatasetFilterProcessorBase {

  /**
   * {@inheritdoc}
   */
  public function isValid(string $dataset_id): bool {
    return $this->dataStorage->isPublished($dataset_id);
  }

}
