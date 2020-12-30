<?php

namespace Drupal\harvest;

/**
 * Trait OrphanDatasetsProcessor.
 *
 * @package Drupal\harvest
 */
trait OrphanDatasetsProcessor {

  /**
   * Find and, if any, process the datasets orphaned by this harvest.
   *
   * @param string $harvestId
   *   Harvest identifier.
   * @param array $extractedIds
   *   List of dataset identifiers extracted by this harvest.
   *
   * @return array
   *   Orphan dataset identifiers.
   */
  private function processOrphanDatasets(string $harvestId, array $extractedIds) : array {

    $lastRunId = $this->getLastHarvestRunId($harvestId);
    if (!$lastRunId) {
      return [];
    }

    $orphanIds = $this->findOrphansFromSpecificRun($harvestId, $lastRunId, $extractedIds);
    $this->processOrphanDatasetIds($orphanIds);

    return $orphanIds;
  }

  /**
   * Find dataset identifiers orphaned by a harvest's specific run.
   *
   * @param string $harvestId
   *   Harvest identifier.
   * @param string $runId
   *   Harvest run identifier.
   * @param array $extractedIds
   *   List of dataset identifiers extracted by this harvest.
   *
   * @return array
   *   Orphan dataset identifiers.
   */
  private function findOrphansFromSpecificRun(string $harvestId, string $runId, array $extractedIds) {
    $runInfo = json_decode($this->getHarvestRunInfo($harvestId, $runId));
    $previouslyExtractedIds = $runInfo->status->extracted_items_ids;
    return array_values(array_diff($previouslyExtractedIds, $extractedIds));
  }

  /**
   * Orphan a list of datasets.
   *
   * @param array $orphanIds
   *   Orphan dataset identifiers.
   */
  private function processOrphanDatasetIds(array $orphanIds) {

    $nodeStorage = $this->entityTypeManager->getStorage('node');

    foreach ($orphanIds as $uuid) {
      $datasets = $nodeStorage->loadByProperties(['uuid' => $uuid]);
      if (FALSE !== ($dataset = reset($datasets))) {
        $dataset->set('moderation_state', 'orphaned');
        $dataset->save();
      }
    }
  }

}
