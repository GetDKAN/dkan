<?php

namespace Drupal\harvest_dashboard\Controller;

use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Controller.
 */
class Controller {

  /**
   * A list of harvests and some status info.
   */
  public function harvests(): array {

    $harvestsHeader = [
      'Harvest ID',
      'Extract Status',
      'Last Run',
      '# of Datasets',
    ];

    /** @var \Drupal\harvest\Service $harvestService */
    $harvestService = \Drupal::service('dkan.harvest.service');

    date_default_timezone_set('EST');

    $rows = [];
    foreach ($harvestService->getAllHarvestIds() as $harvestId) {
      $runIds = $harvestService->getAllHarvestRunInfo($harvestId);

      if ($runId = end($runIds)) {
        $info = json_decode($harvestService->getHarvestRunInfo($harvestId, $runId));

        $rows[] = $this->buildHarvestRow($harvestId, $runId, $info);
      }
    }

    return [
      '#theme' => 'table',
      '#header' => $harvestsHeader,
      '#rows' => $rows,
    ];
  }

  /**
   * Private.
   */
  private function buildHarvestRow(string $harvestId, string $runId, $info) {
    $url = Url::fromRoute("dkan.harvest.dashboard.datasets", ['harvestId' => $harvestId]);

    return [
      'harvest_link' => Link::fromTextAndUrl($harvestId, $url),
      'extract_status' => $info->status->extract,
      'last_run' => date('m/d/y H:m:s T', $runId),
      'dataset_count' => count(array_keys((array) $info->status->load)),
    ];
  }

  /**
   * Datasets information for a specific harvest.
   */
  public function harvestDatasets($harvestId) {

    $datasetsHeader = [
      'Dataset UUID',
      'Dataset Title',
      'Revision ID',
      'Publication Status',
      'Harvest Status',
      'Modified Date Metadata',
      'Modified Date DKAN',
      'Resources',
    ];

    $load = $this->getHarvestLoadStatus($harvestId);
    $datasets = array_keys($load);

    /** @var \Drupal\common\DatasetInfo $datasetInfoService */
    $datasetInfoService = \Drupal::service('dkan.common.dataset_info');

    $rows = [];
    foreach ($datasets as $datasetId) {
      $datasetInfo = $datasetInfoService->gather($datasetId);
      $datasetRow = $this->buildDatasetRow($datasetInfo, $load[$datasetId]);
      $rows = array_merge($rows, $datasetRow);
    }

    return [
      '#theme' => 'table',
      '#header' => $datasetsHeader,
      '#rows' => $rows,
    ];
  }

  /**
   * May build 2 rows if data has both published and draft version.
   */
  private function buildDatasetRow(array $revisions, string $harvestStatus) : array {
    $rows = [];

    foreach ($revisions as $revision) {
      $rows[] = [
        $revision['uuid'],
        $revision['title'],
        $revision['revision_id'],
        $revision['moderation_state'],
        $harvestStatus,
        $revision['modified_date_metadata'],
        $revision['modified_date_dkan'],
        $this->buildResourcesTable($revision['distributions']),
      ];
    }

    return $rows;
  }

  /**
   * Private.
   */
  private function buildResourcesTable(array $distributions) {

    $distributionsHeader = [
      'Distribution UUID',
      'Fetch',
      '%',
      'Store',
      '%',
    ];

    $rows = [];
    foreach ($distributions as $distribution) {
      $rows[] = [
        $distribution['distribution_uuid'],
        $distribution['fetcher_status'],
        $distribution['fetcher_percent_done'],
        $distribution['importer_status'],
        $distribution['importer_percent_done'],
      ];
    }

    $build['resourcesTable'] = [
      '#theme' => 'table',
      '#header' => $distributionsHeader,
      '#rows' => $rows,
      '#empty' => 'No resources',
    ];

    return render($build);
  }

  /**
   * Private.
   */
  private function getHarvestLoadStatus($harvestId): array {
    $harvest = \Drupal::service('dkan.harvest.service');

    $runIds = $harvest->getAllHarvestRunInfo($harvestId);
    $runId = end($runIds);

    $json = $harvest->getHarvestRunInfo($harvestId, $runId);
    $info = json_decode($json);
    $status = $info->status;
    return (array) $status->load;
  }

}
