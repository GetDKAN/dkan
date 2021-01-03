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
  private function getOrphanIdsFromResult(string $harvestId, array $extractedIds) : array {

    $lastRunId = $this->getLastHarvestRunId($harvestId);
    if (!$lastRunId) {
      return [];
    }

    $orphanIds = $this->findOrphansFromSpecificRun($harvestId, $lastRunId, $extractedIds);

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
  public function processOrphanIds(array $orphanIds) {

    $nodeStorage = $this->entityTypeManager->getStorage('node');

    foreach ($orphanIds as $uuid) {
      $datasets = $nodeStorage->loadByProperties(['uuid' => $uuid]);
      if (FALSE !== ($dataset = reset($datasets))) {
        $dataset->set('moderation_state', 'orphaned');
        $dataset->save();
      }
    }
  }

  public function getOrphansFromCompleteHarvest(string $harvestId) {

    $cumulativelyRemovedIds = [];
    $runIds = $this->getAllHarvestRunInfo($harvestId);

    // Initialize with the first harvest run.
    $previousRunId = array_shift($runIds);
    $previousExtractedIds = $this->getExtractedIds($harvestId,$previousRunId);

    foreach ($runIds as $runId) {
      $extractedIds = $this->getExtractedIds($harvestId, $runId);

      // Find and keep track of removed identifiers.
      $removed = array_diff($previousExtractedIds, $extractedIds);
      $cumulativelyRemovedIds = array_unique(array_merge($cumulativelyRemovedIds, $removed));
      // Find but do not keep track of (re-)added identifiers.
      $added = array_diff($extractedIds, $previousExtractedIds);
      $cumulativelyRemovedIds = array_diff($cumulativelyRemovedIds, $added);

      // Set up the next iteration by re-using this known result.
      $previousExtractedIds = $extractedIds;
    }

    return $cumulativelyRemovedIds;
  }

  /**
  * Get ids extracted by a specific harvest run.
  */
  private function getExtractedIds(string $harvestId, string $runId) : array {
    $runInfo = json_decode($this->getHarvestRunInfo($harvestId, $runId));
    return $runInfo->status->extracted_items_ids;
  }

}
