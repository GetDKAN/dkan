<?php

namespace Drupal\dkan_metastore\Reference\ResourceDiscovery;

use Drupal\dkan_common\DataResource;

/**
 * Service class for discovering resources from dataset metadata.
 */
class DatasetResourceDiscovery implements ResourceDiscoveryInterface {

  /**
   * {@inheritdoc}
   */
  public function discoverResources(object $metadata): ResourceDiscoveryResult {
    $dataset_id = $metadata->identifier ?? NULL;
    if (!$dataset_id) {
      throw new \InvalidArgumentException('Metadata object must contain an identifier property.');
    }
    $result = new ResourceDiscoveryResult($dataset_id);
    foreach (($metadata->distribution ?? []) as $distribution) {
      $this->processDistribution($distribution, $result);
    }

    return $result;
  }

  /**
   * Process a single distribution and add it to the result.
   *
   * @param object $distribution
   *   The distribution object from the metadata.
   * @param \Drupal\dkan_metastore\Reference\ResourceDiscovery\ResourceDiscoveryResult $result
   *   The result object to which resources and entries will be added.
   */
  protected function processDistribution(object $distribution, ResourceDiscoveryResult $result): void {
    if (!isset($distribution->downloadURL)) {
      $result->addSkippedEntry(new SkippedEntry(
        '',
        $distribution->mediaType ?? 'unknown',
        SkippedEntryReasons::MissingUrl,
      ));
      return;
    }

    // Check that URL is valid.
    if (filter_var($distribution->downloadURL, FILTER_VALIDATE_URL) === FALSE) {
      $result->addSkippedEntry(new SkippedEntry(
        $distribution->downloadURL,
        $distribution->mediaType ?? 'unknown',
        SkippedEntryReasons::InvalidUrl,
      ));
      return;
    }

    $resource = new DataResource(
      file_path: $distribution->downloadURL,
      mimeType: $distribution->mediaType
    );
    $result->addDiscoveredResource($resource);
  }

}
