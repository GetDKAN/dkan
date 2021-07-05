<?php

namespace Drupal\datastore\Controller;

use Drupal\common\DatasetInfo;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\harvest\Service;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;

/**
 * Class Api.
 *
 * @package Drupal\datastore
 *
 * @codeCoverageIgnore
 */
class DashboardController implements ContainerInjectionInterface {
  use StringTranslationTrait;

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
   * DashboardController constructor.
   *
   * @param \Drupal\harvest\Service $harvestService
   *   Harvest service.
   * @param \Drupal\common\DatasetInfo $datasetInfo
   *   Dataset information service.
   */
  public function __construct(Service $harvestService, DatasetInfo $datasetInfo) {
    $this->harvest = $harvestService;
    $this->datasetInfo = $datasetInfo;
  }

  /**
   * Create controller object from dependency injection container.
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('dkan.harvest.service'),
      $container->get('dkan.common.dataset_info')
    );
  }

  /**
   * Datasets information.
   */
  public function datasetsImportStatus($harvestId) {
    $harvestIds = !empty($harvestId) ? [$harvestId] : $this->harvest->getAllHarvestIds();

    $load = [];
    foreach ($harvestIds as $harvestId) {
      $load += $this->getHarvestLoadStatus($harvestId);
    }
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
   * Datasets information. Title callback.
   */
  public function datasetsImportStatusTitle($harvestId) {
    $defaultTitle = 'Datastore Import Status';
    if (!empty($harvestId)) {
      return $this->t($defaultTitle .= ". Harvest %harvest", ['%harvest' => $harvestId]);
    }
    return $this->t($defaultTitle);
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

}
