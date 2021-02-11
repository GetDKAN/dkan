<?php

namespace Drupal\harvest_dashboard\Controller;

use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Controller.
 */
class Controller {

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

    $harvestsHeader = [
      'Harvest ID',
      'Extract Status',
      'Last Run',
      '# of Datasets',
    ];

    date_default_timezone_set('EST');

    $rows = [];
    foreach ($this->harvest->getAllHarvestIds() as $harvestId) {
      $runIds = $this->harvest->getAllHarvestRunInfo($harvestId);

      if ($runId = end($runIds)) {
        $info = json_decode($this->harvest->getHarvestRunInfo($harvestId, $runId));

        $rows[] = $this->buildHarvestRow($harvestId, $runId, $info);
      }
    }

    return [
      '#theme' => 'table',
      '#header' => $harvestsHeader,
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

    $rows = [];
    foreach ($datasets as $datasetId) {
      $datasetInfo = $this->datasetInfo->gather($datasetId);
      $datasetRow = $this->buildDatasetRow($datasetInfo, $load[$datasetId]);
      $rows = array_merge($rows, $datasetRow);
    }

    return [
      '#theme' => 'table',
      '#header' => $datasetsHeader,
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
      $row = [];

      if ($count > 1) {
        if ($i == 0) {
          $row[] = ['data' => $rev['uuid'], 'rowspan' => $count];
        }
      }
      else {
        $row[] = $rev['uuid'];
      }

      $row[] = $rev['title'];
      $row[] = $rev['revision_id'];
      $row[] = ['data' => $rev['moderation_state'], 'class' => $rev['moderation_state']];
      $row[] = ['data' => $harvestStatus, 'class' => strtolower($harvestStatus)];
      $row[] = $rev['modified_date_metadata'];
      $row[] = $rev['modified_date_dkan'];
      $row[] = $this->buildResourcesTable($rev['distributions']);

      $rows[] = $row;
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
    foreach ($distributions as $dist) {
      $rows[] = [
        $dist['distribution_uuid'],
        [
          'data' => $dist['fetcher_status'],
          'class' => $dist['fetcher_status'] == 'in_progress' ? 'in-progress' : $dist['fetcher_status'],
        ],
        [
          'data' => $dist['fetcher_percent_done'],
          'class' => (int) $dist['fetcher_percent_done'] == 100 ? 'done' : 'in-progress',
        ],
        [
          'data' => $dist['importer_status'],
          // stopped, in_progress, error, done.
          'class' => $dist['importer_status'] == 'in_progress' ? 'in-progress' : $dist['importer_status'],
        ],
        [
          'data' => $dist['importer_percent_done'],
          'class' => (int) $dist['importer_percent_done'] == 100 ? 'done' : 'in-progress',
        ],
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
    $runIds = $this->harvest->getAllHarvestRunInfo($harvestId);
    $runId = end($runIds);

    $json = $this->harvest->getHarvestRunInfo($harvestId, $runId);
    $info = json_decode($json);
    $status = $info->status;
    return (array) $status->load;
  }

}
