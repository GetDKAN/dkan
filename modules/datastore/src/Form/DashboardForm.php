<?php

namespace Drupal\datastore\Form;

use Drupal\Core\Pager\PagerManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

use Drupal\common\DatasetInfo;
use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\DependencyInjection\ContainerNotInitializedException;
use Drupal\Core\Render\Element\Link;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Drupal\common\UrlHostTokenResolver;
use Drupal\harvest\Service;
use Drupal\metastore\Service as MetastoreService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Datastore Import Dashboard form.
 *
 * @package Drupal\datastore
 */
class DashboardForm extends FormBase {
  use StringTranslationTrait;

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
    PagerManagerInterface $pagerManager,
    DateFormatter $dateFormatter
  ) {
    $this->harvest = $harvestService;
    $this->datasetInfo = $datasetInfo;
    $this->metastore = $metastoreService;
    $this->pagerManager = $pagerManager;
    $this->dateFormatter = $dateFormatter;
    $this->itemsPerPage = 10;
  }

  /**
   * Create controller object from dependency injection container.
   */
  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('dkan.harvest.service'),
      $container->get('dkan.common.dataset_info'),
      $container->get('dkan.metastore.service'),
      $container->get('pager.manager'),
      $container->get('date.formatter')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dashboard_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    // Set the method.
    $form_state->setMethod('GET');
    // Fetch GET parameter.
    $params = $this->getParameters();
    // Add custom after_build method to remove unnecessary GET parameters.
    $form['#after_build'] = ['::afterBuild'];
    $form['#attached'] = ['library' => ['datastore/style']];

    // Build dataset import status table render array.
    return $form + $this->buildFilters($params) + $this->buildTable($this->getDatasets($params));
  }

  /**
   * Fetch request GET parameters.
   *
   * @return array
   *   Request GET parameters.
   */
  protected function getParameters(): array {
    return ($request = $this->getRequest()) && isset($request->query) ? array_filter($request->query->all()) : [];
  }

  /**
   * Custom after build callback method.
   */
  public function afterBuild(array $element, FormStateInterface $form_state): array {
    // Remove the form_token, form_build_id, form_id, and op from the GET
    // parameters.
    unset($element['form_token'], $element['form_build_id'], $element['form_id'], $element['filters']['actions']['submit']['#name']);

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {}

  /**
   * Build datasets import status table filters.
   *
   * @param string[] $filters
   *   Dataset filters.
   *
   * @return array[]
   *   Table filters render array.
   */
  protected function buildFilters(array $filters): array {
    // Retrieve potential harvest IDs for "Harvest ID" filter.
    $harvestIds = $this->harvest->getAllHarvestIds();

    return [
      'filters' => [
        '#type' => 'container',
        '#attributes' => ['class' => ['form--inline', 'clearfix']],
        'uuid' => [
          '#type' => 'textfield',
          '#weight' => 1,
          '#title' => $this->t('Dataset ID'),
          '#default_value' => $filters['uuid'] ?? '',
        ],
        'harvest_id' => [
          '#type' => 'select',
          '#weight' => 1,
          '#title' => $this->t('Harvest ID'),
          '#default_value' => $filters['harvest_id'] ?? '',
          '#empty_option' => $this->t('- None -'),
          '#options' => array_combine($harvestIds, $harvestIds),
        ],
        'actions' => [
          '#type' => 'actions',
          '#weight' => 2,
          'submit' => [
            '#type' => 'submit',
            '#value' => $this->t('Filter'),
            '#button_type' => 'primary',
          ],
        ],
      ],
    ];
  }

  /**
   * Build datasets import status table.
   *
   * @param string[] $datasets
   *   Dataset UUIDs to be displayed.
   *
   * @return array[]
   *   Table render array.
   */
  public function buildTable(array $datasets): array {
    return [
      'table' => [
        '#theme' => 'table',
        '#weight' => 3,
        '#header' => $this->getDatasetTableHeader(),
        '#rows' => $this->buildDatasetRows($datasets),
        '#attributes' => ['class' => 'dashboard-datasets'],
        '#attached' => ['library' => ['harvest/style']],
        '#empty' => 'No datasets found',
      ],
      'pager' => [
        '#type' => 'pager',
        '#weight' => 5,
      ],
    ];
  }

  /**
   * Retrieve list of UUIDs for datasets matching the given filters.
   *
   * @param string[] $filters
   *   Datasets filters.
   *
   * @return string[]
   *   Filtered list of dataset UUIDs.
   */
  protected function getDatasets(array $filters): array {
    $datasets = [];

    // If a value was supplied for the UUID filter, include only it in the list
    // of dataset UUIDs returned.
    if (isset($filters['uuid'])) {
      $datasets = [$filters['uuid']];
    }
    // If a value was supplied for the harvest ID filter, retrieve dataset UUIDs
    // belonging to the specfied harvest.
    elseif (isset($filters['harvest_id'])) {
      $harvestLoad = $this->getHarvestLoadStatus($filters['harvest_id']);
      $datasets = array_keys($harvestLoad);
      $total = count($datasets);
      $currentPage = $this->pagerManager->createPager($total, $this->itemsPerPage)->getCurrentPage();

      $chunks = array_chunk($datasets, $this->itemsPerPage) ?: [[]];
      $datasets = $chunks[$currentPage];
    }
    // If no filter values were supplied, fetch from the list of all dataset
    // UUIDs.
    else {
      $total = $this->metastore->count('dataset');
      $currentPage = $this->pagerManager->createPager($total, $this->itemsPerPage)->getCurrentPage();
      $datasets = $this->metastore->getRangeUuids('dataset', ($currentPage * $this->itemsPerPage), $this->itemsPerPage);
    }

    return $datasets;
  }

  /**
   * Builds dataset rows array.
   *
   * @param string[] $datasets
   *   Dataset UUIDs for which to generate dataset rows.
   *
   * @return array
   *   Table rows.
   */
  protected function buildDatasetRows(array $datasets): array {
    // Fetch the status of all harvests.
    $harvestLoad = iterator_to_array($this->getHarvestLoadStatuses());

    $rows = [];
    // Build dataset rows fore each of the supplied dataset UUIDs.
    foreach ($datasets as $datasetId) {
      // Gather dataset information.
      $datasetInfo = $this->datasetInfo->gather($datasetId);
      if (empty($datasetInfo['latest_revision'])) {
        continue;
      }
      // Build a table row using it's details and harvest status.
      $datasetRow = $this->buildRevisionRows($datasetInfo, $harvestLoad[$datasetId] ?? 'N/A');
      $rows = array_merge($rows, $datasetRow);
    }

    return $rows;
  }

  /**
   * Fetch the status of all harvests.
   */
  protected function getHarvestLoadStatuses(): \Generator {
    foreach ($this->harvest->getAllHarvestIds() as $harvestId) {
      yield from $this->getHarvestLoadStatus($harvestId);
    }
  }

  /**
   * Fetch the status of datasets belonging to the given harvest.
   *
   * @param string|null $harvestId
   *   Harvest ID to search for.
   *
   * @return string[]
   *   Harvest statuses keyed by dataset UUIDs.
   */
  protected function getHarvestLoadStatus(?string $harvestId): array {
    $runIds = $this->harvest->getAllHarvestRunInfo($harvestId);
    $runId = end($runIds);

    $json = $this->harvest->getHarvestRunInfo($harvestId, $runId);
    $info = json_decode($json);
    $loadExists = isset($info->status) && isset($info->status->load);

    return $loadExists ? (array) $info->status->load : [];
  }

  /**
   * Create the header array for table template.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup[]
   *   Array of table headers.
   */
  protected function getDatasetTableHeader(): array {
    return [
      $this->t('Dataset'),
      $this->t('Revision'),
      $this->t('Harvest'),
      $this->t('Resource'),
      $this->t('Fetch'),
      $this->t('Store'),
    ];
  }

  /**
   * Build dataset row(s) for the given dataset revision information.
   *
   * This method may build 2 rows if data has both published and draft version.
   *
   * @param array $datasetInfo
   *   Dataset information, result of \Drupal\common\DatasetInfo::gather().
   * @param string $harvestStatus
   *   Dataset harvest status.
   *
   * @return array[]
   *   Dataset revision rows.
   */
  protected function buildRevisionRows(array $datasetInfo, string $harvestStatus) : array {
    $rows = [];

    // Create a row for each dataset revision (there could be both a published
    // and latest).
    foreach (array_values($datasetInfo) as $rev) {
      $modified = $this->dateFormatter->format(strtotime($rev['modified_date_dkan']), 'short');
      $distributions = $rev['distributions'];
      $rows[] = array_merge(
        [
          [
            'rowspan' => count($distributions),
            'data' => [
              '#theme' => 'datastore_dashboard_dataset_cell',
              '#uuid' => $rev['uuid'],
              '#title' => $rev['title'],
              '#url' => Url::fromUri("internal:/dataset/$rev[uuid]"),
            ],
          ],
          [
            'rowspan' => count($distributions),
            'class' => $rev['moderation_state'],
            'data' => [
              '#theme' => 'datastore_dashboard_revision_cell',
              '#revision_id' => $rev['revision_id'],
              '#modified' => $modified,
              '#moderation_state' => $rev['moderation_state'],
            ],
          ],
          [
            'rowspan' => count($distributions),
            'data' => $harvestStatus,
            'class' => strtolower($harvestStatus)
          ],
        ],
        $this->buildResourcesRow(array_shift($distributions))
      );
      while (!empty($distributions)) {
        $rows[] = $this->buildResourcesRow(array_shift($distributions));
      }
    }

    return $rows;
  }

  /**
   * Build resources table using the supplied distributions.
   *
   * @param array[] $distributions
   *   Distribution details.
   *
   * @return array
   *   Distribution table render array.
   */
  protected function buildResourcesRow(array $dist): array {
    if (isset($dist['distribution_uuid'])) {
      return [
        [
          'data' => [
            '#theme' => 'datastore_dashboard_resource_cell',
            '#uuid' => $dist['distribution_uuid'],
            '#file_name' => basename($dist['source_path']),
            '#file_path' => UrlHostTokenResolver::resolve($dist['source_path']),
          ],
        ],
        [
          'data' => [
            '#theme' => 'datastore_dashboard_status_cell',
            '#status' => $dist['fetcher_status'],
            '#percent' => $dist['fetcher_percent_done'],
          ],
          'class' => str_replace('_', '-', $dist['fetcher_status']),
        ],
        [
          'data' => [
            '#theme' => 'datastore_dashboard_status_cell',
            '#status' => $dist['importer_status'],
            '#percent' => $dist['importer_percent_done'] ?: "0",
            '#error' => $this->cleanUpError($dist['importer_error']),
          ],
          'class' => str_replace('_', '-', $dist['importer_status']),
        ],
      ];
    }
  }

  /**
   * Build resource status cell.
   *
   * @param string $status
   *   Resource status.
   *
   * @return string[]
   *   Resource status cell render array.
   */
  protected function statusCell(string $status, int $percent, $error = ''): array {
    $error = $this->cleanUpError((string) $error);
    $detail = $error ?: "$percent%";
    return [
      'data' => "$status ($detail)",
      'class' => $status == 'in_progress' ? 'in-progress' : $status,
    ];
  }

  /**
   * Tidy up error message from MySQL for display.
   *
   * @param mixed $error
   *   An error message. Will be cast to string.
   *
   * @return string
   *   The sanitized error message.
   */
  private function cleanUpError($error) {
    $error = (string) $error;
    $mysqlErrorPattern = '/^SQLSTATE\[[A-Z0-9]+\]: .+?: [0-9]+ (.+?): [A-Z]/';
    if (preg_match($mysqlErrorPattern, $error, $matches)) {
      return $matches[1];
    }
    return $error;
  }

  /**
   * Build resource progress percentage cell.
   *
   * @param int $percent
   *   Resource progress percentage.
   *
   * @return string[]
   *   Resource percent cell render array.
   */
  protected function percentCell(int $percent): array {
    return [
      'data' => $percent,
      'class' => $percent == 100 ? 'done' : 'in-progress',
    ];
  }

}
