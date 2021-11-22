<?php

namespace Drupal\datastore\Controller;

use Drupal\common\DatasetInfo;
use Drupal\Core\Pager\PagerManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\harvest\Service;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\metastore\Service as MetastoreService;

/**
 * Class Api.
 *
 * @package Drupal\datastore
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
   * Metastore service.
   *
   * @var \Drupal\metastore\Service
   */
  protected $metastore;

  /**
   * Pager manager service.
   *
   * @var \Drupal\Core\Pager\PagerManagerInterface
   */
  protected $pagerManager;

  /**
   * Items per page.
   *
   * @var int
   */
  protected $itemsPerPage;

  /**
   * DashboardController constructor.
   *
   * @param \Drupal\harvest\Service $harvestService
   *   Harvest service.
   * @param \Drupal\common\DatasetInfo $datasetInfo
   *   Dataset information service.
   * @param \Drupal\metastore\Service $metastoreService
   *   Metastore service.
   * @param \Drupal\Core\Pager\PagerManagerInterface $pagerManager
   *   Pager manager service.
   */
  public function __construct(
    Service $harvestService,
    DatasetInfo $datasetInfo,
    MetastoreService $metastoreService,
    PagerManagerInterface $pagerManager
  ) {
    $this->harvest = $harvestService;
    $this->datasetInfo = $datasetInfo;
    $this->metastore = $metastoreService;
    $this->pagerManager = $pagerManager;
    $this->itemsPerPage = 10;
  }

  /**
   * Create controller object from dependency injection container.
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('dkan.harvest.service'),
      $container->get('dkan.common.dataset_info'),
      $container->get('dkan.metastore.service'),
      $container->get('pager.manager')
    );
  }

  /**
   * Build datasets import status table.
   */
  public function buildDatasetsImportStatusTable($harvestId) {
    return [
      'table' => [
        '#theme' => 'table',
        '#header' => self::DATASET_HEADERS,
        '#rows' => $this->buildDatasetRows($harvestId),
        '#attributes' => ['class' => 'dashboard-datasets'],
        '#attached' => ['library' => ['harvest/style']],
        '#empty' => 'No datasets found',
      ],
      'pager' => [
        '#type' => 'pager',
      ],
    ];
  }

  /**
   * Build datasets import status table title.
   */
  public function buildDatasetsImportStatusTitle($harvestId) {
    if (!empty($harvestId)) {
      return $this->t("Datastore Import Status. Harvest %harvest", ['%harvest' => $harvestId]);
    }
    return $this->t('Datastore Import Status');
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
   * Builds dataset rows array to be themed as a table.
   *
   * @param array|null $harvestId
   *   Harvest ID for which to generate dataset rows.
   *
   * @return array
   *   Table rows.
   */
  private function buildDatasetRows(?string $harvestId): array {
    $rows = [];
    foreach ($this->getDatasetsWithHarvestId($harvestId) as $datasetId => $status) {
      $datasetInfo = $this->datasetInfo->gather($datasetId);
      if (empty($datasetInfo['latest_revision'])) {
        continue;
      }
      $datasetRow = $this->buildDatasetRow($datasetInfo, $status);
      $rows = array_merge($rows, $datasetRow);
    }
    return $rows;
  }

  /**
   * Retrieve datasets and import status belonging to the given harvest ID.
   *
   * @param string|null $harvestId
   *   Harvest ID which fetched datasets should belong to.
   *
   * @return string[]
   *   Dataset import statuses keyed by their dataset IDs.
   */
  protected function getDatasetsWithHarvestId(?string $harvestId): array {
    if (!empty($harvestId)) {
      $harvestLoad = $this->getHarvestLoadStatus($harvestId);
      $datasets = array_keys($harvestLoad);
      $total = count($datasets);
      $currentPage = $this->pagerManager->createPager($total, $this->itemsPerPage)->getCurrentPage();

      $chunks = array_chunk($datasets, $this->itemsPerPage);
      $datasets = $chunks[$currentPage];
    }
    else {
      $harvestLoad = [];
      foreach ($this->harvest->getAllHarvestIds() as $harvestId) {
        $harvestLoad += $this->getHarvestLoadStatus($harvestId);
      }
      $total = $this->metastore->count('dataset');
      $currentPage = $this->pagerManager->createPager($total, $this->itemsPerPage)->getCurrentPage();
      $datasets = $this->metastore->getRangeUuids('dataset', $currentPage, $this->itemsPerPage);
      $datasets = array_replace(array_fill_keys($datasets, 'N/A'), $harvestLoad);
    }

    return $datasets;
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
      if (isset($dist['distribution_uuid'])) {
        $rows[] = [
          $dist['distribution_uuid'],
          $this->statusCell($dist['fetcher_status']),
          $this->percentCell($dist['fetcher_percent_done']),
          $this->statusCell($dist['importer_status']),
          $this->percentCell($dist['importer_percent_done']),
        ];
      }
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
