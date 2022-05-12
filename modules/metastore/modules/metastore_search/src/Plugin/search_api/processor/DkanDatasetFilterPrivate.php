<?php

namespace Drupal\metastore_search\Plugin\search_api\processor;

use Drupal\metastore\Service;
use Drupal\metastore_search\Plugin\search_api\DkanDatasetFilterProcessorBase;

/**
 * Excludes datasets with private accessLevel from data indexes.
 *
 * @SearchApiProcessor(
 *   id = "dkan_dataset_filter_private",
 *   label = @Translation("DKAN Dataset Filter Private"),
 *   description = @Translation("Exclude private datasets from being indexed."),
 *   stages = {
 *     "alter_items" = 0
 *   }
 * )
 */
class DkanDatasetFilterPrivate extends DkanDatasetFilterProcessorBase {

  /**
   * {@inheritdoc}
   */
  public function isValid(string $dataset_id): bool {

    // @todo Check if plugin weights could be used to check isPublished first.
    if (!$this->dataStorage->isPublished($dataset_id)) {
      return FALSE;
    }

    // @todo Dependency injection.
    /** @var \Drupal\metastore\Service $metastoreService */
    $metastoreService = \Drupal::service('dkan.metastore.service');
    $dataset = $metastoreService->get('dataset', $dataset_id);

    return $dataset->{'$.accessLevel'} !== 'private';
  }

}
