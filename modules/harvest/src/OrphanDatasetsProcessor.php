<?php

namespace Drupal\harvest;

trait OrphanDatasetsProcessor {

  /**
   * Add the identifiers of datasets orphaned by this harvest, if any.
   *
   * @param string $harvestId
   *   Harvest identifier.
   * @param array $result
   *   Harvest result.
   *
   * @return array
   *   Orphan dataset identifiers.
   */
  private function addOrphanDatasets(string $harvestId, array $result) : array {

    $lastRunId = $this->getLastHarvestRunId($harvestId);
    if (!$lastRunId) {
      $result['status']['orphan_ids'] = [];
      return $result;
    }

    $lastRunInfo = json_decode($this->getHarvestRunInfo($harvestId, $lastRunId));
    $previouslyExtractedIds = $lastRunInfo->status->extracted_items_ids;
    $latestExtractedIds = $result['status']['extracted_items_ids'];
    $result['status']['orphan_ids'] = array_values(array_diff($previouslyExtractedIds, $latestExtractedIds));
    return $result;
  }

  /**
   * Process orphan datasets.
   *
   * @param array $result
   *   Harvest result.
   */
  private function processOrphanDatasets(array $result) {
    if (empty($result['status']['orphan_ids'])) {
      return;
    }

    $nodeStorage = $this->entityTypeManager->getStorage('node');

    foreach ($result['status']['orphan_ids'] as $uuid) {
      $datasets = $nodeStorage->loadByProperties(['uuid' => $uuid]);
      if (FALSE !== ($dataset = reset($datasets))) {
        $dataset->set('moderation_state', 'orphaned');
        $dataset->save();
      }
    }
  }

}
