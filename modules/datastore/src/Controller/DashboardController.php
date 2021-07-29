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
   * Datasets information.
   */
  public function datasetsImportStatus($harvestId) {
    if (!empty($harvestId)) {
      $harvestLoad = $this->getHarvestLoadStatus($harvestId);
      $datasets = array_keys($harvestLoad);
    }
    else {
      $harvestLoad = [];
      foreach ($this->harvest->getAllHarvestIds() as $harvestId) {
        $harvestLoad += $this->getHarvestLoadStatus($harvestId);
      }
      $datasets = $this->getAllDatasetUuids();
    }

    $rows = $this->buildDatasetRows($datasets, $harvestLoad);

    return [
      'table' => [
        '#theme' => 'table',
        '#header' => self::DATASET_HEADERS,
        '#rows' => $this->pagerArray($rows, $this->itemsPerPage),
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
   * Gets all dataset uuids from metadata.
   *
   * @return array
   *   Dataset uuids array.
   */
  private function getAllDatasetUuids() : array {
    return array_map(function ($datasetMetadata) {
      return $datasetMetadata->{"$.identifier"};
    }, $this->metastore->getAll('dataset'));
  }

  /**
   * Datasets information. Title callback.
   */
  public function datasetsImportStatusTitle($harvestId) {
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
   * @param array $datasets
   *   Dataset uuids array.
   * @param array $harvestLoad
   *   Harvest statuses by dataset array.
   *
   * @return array
   *   Table rows.
   */
  private function buildDatasetRows(array $datasets, array $harvestLoad) {
    $rows = [];
    foreach ($datasets as $datasetId) {
      $datasetInfo = $this->datasetInfo->gather($datasetId);
      if (empty($datasetInfo['latest_revision'])) {
        continue;
      }
      $harvestStatus = isset($harvestLoad[$datasetId]) ? $harvestLoad[$datasetId] : 'N/A';
      $datasetRow = $this->buildDatasetRow($datasetInfo, $harvestStatus);
      $rows = array_merge($rows, $datasetRow);
    }
    return $rows;
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

  /**
   * Returns pager array.
   *
   * @param array $items
   *   Table rows.
   * @param int $itemsPerPage
   *   Items per page.
   *
   * @return array
   *   Table rows chunk.
   */
  private function pagerArray(array $items, int $itemsPerPage) : array {
    $total = count($items);
    $currentPage = $this->pagerManager->createPager($total, $itemsPerPage)->getCurrentPage();
    $chunks = array_chunk($items, $itemsPerPage);
    return !empty($chunks) ? $chunks[$currentPage] : [];
  }

}
