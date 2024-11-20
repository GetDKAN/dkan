<?php

namespace Drupal\harvest;

use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Handle dataset orphaning.
 *
 * @package Drupal\harvest
 */
trait OrphanDatasetsProcessor {

  /**
   * Entity type manager.
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * Setter for this trait's entity type manager service dependency.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   */
  public function setEntityTypeManager(EntityTypeManagerInterface $entityTypeManager) : void {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Get the dataset identifiers orphaned by the harvest currently in progress.
   */
  private function getOrphanIdsFromResult(string $harvestId, array $extractedIds) : array {
    if ($lastRunId = $this->runRepository->getLastHarvestRunId($harvestId)) {
      $previouslyExtractedIds = $this->getExtractedIds($harvestId, $lastRunId);
      return array_values(array_diff($previouslyExtractedIds, $extractedIds));
    }
    return [];
  }

  /**
   * Get ids extracted by a specific harvest run.
   */
  private function getExtractedIds(string $harvestId, string $runId) : array {
    return $this->runRepository->getExtractedUuids($harvestId, $runId);
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

  /**
   * Get orphan datasets from a harvest's cumulative runs.
   *
   * @param string $harvestId
   *   Harvest identifier.
   *
   * @return array
   *   Array of dataset identifiers removed by this harvest.
   */
  public function getOrphanIdsFromCompleteHarvest(string $harvestId) : array {

    $cumulativelyRemovedIds = [];
    $runIds = $this->getRunIdsForHarvest($harvestId);

    // Initialize with the first harvest run.
    if ($previousRunId = array_shift($runIds)) {
      $previousExtractedIds = $this->getExtractedIds($harvestId, $previousRunId);

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
    }

    return $cumulativelyRemovedIds;
  }

}
