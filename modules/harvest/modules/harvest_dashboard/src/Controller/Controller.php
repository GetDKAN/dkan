<?php

namespace Drupal\harvest_dashboard\Controller;

use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Controller.
 */
class Controller {

  const HARVEST_HEADERS = [
    'Harvest ID',
    'Extract Status',
    'Last Run',
    '# of Datasets',
  ];

  const DATASET_HEADERS = [
    'Dataset UUID',
    'Dataset Title',
    'Revision ID',
    'Publication Status',
    'Harvest Status',
    'Modified Date Metadata',
    'Modified Date DKAN',
    'Resources',
  ];

  const DISTRIBUTION_HEADERS = [
    'Distribution UUID',
    'Fetch',
    '%',
    'Store',
    '%',
  ];

  /**
   * Harvest service.
   *
   * @var \Drupal\harvest\Service
   */
  protected $harvest;

  /**
   * Dataset information service.
   *
   * @var \Drupal\common\DatasetInfo
   */
  protected $datasetInfo;

  /**
   * Controller constructor.
   */
  public function __construct() {
    $this->harvest = \Drupal::service('dkan.harvest.service');
    $this->datasetInfo = \Drupal::service('dkan.common.dataset_info');
  }

  /**
   * A list of harvests and some status info.
   */
  public function harvests(): array {

    date_default_timezone_set('EST');

    $rows = [];
    foreach ($this->harvest->getAllHarvestIds() as $harvestId) {
      // @todo Make Harvest Service's private getLastHarvestRunId() public,
      //   And replace 7-8 cases where we recreate it.
      $runIds = $this->harvest->getAllHarvestRunInfo($harvestId);

      if ($runId = end($runIds)) {
        $info = json_decode($this->harvest->getHarvestRunInfo($harvestId, $runId));

        $rows[] = $this->buildHarvestRow($harvestId, $runId, $info);
      }
    }

    return [
      '#theme' => 'table',
      '#header' => self::HARVEST_HEADERS,
      '#rows' => $rows,
      '#attributes' => ['class' => 'dashboard-harvests'],
      '#attached' => ['library' => ['harvest_dashboard/style']],
      '#empty' => "No harvests found",
    ];
  }

  /**
   * Private.
   */
  private function buildHarvestRow(string $harvestId, string $runId, $info) {
    $url = Url::fromRoute("dkan.harvest.dashboard.datasets", ['harvestId' => $harvestId]);

    return [
      'harvest_link' => Link::fromTextAndUrl($harvestId, $url),
      'extract_status' => [
        'data' => $info->status->extract,
        'class' => strtolower($info->status->extract),
      ],
      'last_run' => date('m/d/y H:m:s T', $runId),
      'dataset_count' => count(array_keys((array) $info->status->load)),
    ];
  }

  /**
   * Datasets information for a specific harvest.
   */
  public function harvestDatasets($harvestId) {

    $load = $this->getHarvestLoadStatus($harvestId);
    $datasets = array_keys($load);

    $rows = [];
    foreach ($datasets as $datasetId) {
      $datasetInfo = $this->datasetInfo->gather($datasetId);
      if (empty($datasetInfo['latest_revision'])) {
        continue;
      }
      $datasetRow = $this->buildDatasetRow($datasetInfo, $load[$datasetId]);
      $rows = array_merge($rows, $datasetRow);
    }

    return [
      '#theme' => 'table',
      '#header' => self::DATASET_HEADERS,
      '#rows' => $rows,
      '#attributes' => ['class' => 'dashboard-datasets'],
      '#attached' => ['library' => ['harvest_dashboard/style']],
      '#empty' => 'No datasets found',
    ];
  }

  /**
   * May build 2 rows if data has both published and draft version.
   */
  private function buildDatasetRow(array $revisions, string $harvestStatus) : array {
    $rows = [];
    $count = count($revisions);

    foreach (array_values($revisions) as $i => $rev) {
      $firstCell = $this->buildDatasetFirstCell($rev['uuid'], $i, $count);
      $row = isset($firstCell) ? [$firstCell] : [];

      $rows[] = array_merge($row, [
        $rev['title'],
        $rev['revision_id'],
        ['data' => $rev['moderation_state'], 'class' => $rev['moderation_state']],
        ['data' => $harvestStatus, 'class' => strtolower($harvestStatus)],
        $rev['modified_date_metadata'],
        $rev['modified_date_dkan'],
        ['data' => $this->buildResourcesTable($rev['distributions'])],
      ]);;
    }

    return $rows;
  }

  /**
   * Private.
   */
  private function buildDatasetFirstCell(string $uuid, int $i, int $count) {
    if ($count == 1) {
      return ['data' => $uuid];
    }
    else {
      if ($i == 0) {
        return ['data' => $uuid, 'rowspan' => $count];
      }
    }
    return NULL;
  }

  /**
   * Private.
   */
  private function buildResourcesTable(array $distributions) {

    $rows = [];
    foreach ($distributions as $dist) {
      $rows[] = [
        $dist['distribution_uuid'],
        $this->statusCell($dist['fetcher_status']),
        $this->percentCell($dist['fetcher_percent_done']),
        $this->statusCell($dist['importer_status']),
        $this->percentCell($dist['importer_percent_done']),
      ];
    }

    return [
      '#theme' => 'table',
      '#header' => self::DISTRIBUTION_HEADERS,
      '#rows' => $rows,
      '#empty' => 'No resources',
    ];
  }

  /**
   * Private.
   */
  private function statusCell(string $status) {
    return [
      'data' => $status,
      'class' => $status == 'in_progress' ? 'in-progress' : $status,
    ];
  }

  /**
   * Private.
   */
  private function percentCell(int $percent) {
    return [
      'data' => $percent,
      'class' => $percent == 100 ? 'done' : 'in-progress',
    ];
  }

  /**
   * Private.
   */
  private function getHarvestLoadStatus($harvestId): array {
    $runIds = $this->harvest->getAllHarvestRunInfo($harvestId);
    $runId = end($runIds);

    $json = $this->harvest->getHarvestRunInfo($harvestId, $runId);
    $info = json_decode($json);
    $loadExists = isset($info->status) && isset($info->status->load);

    return $loadExists ? (array) $info->status->load : [];
  }

}
