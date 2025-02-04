<?php

namespace Drupal\datastore\Form;

use Drupal\common\DataResource;
use Drupal\Core\Pager\PagerManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\common\DatasetInfo;
use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\Url;
use Drupal\common\UrlHostTokenResolver;
use Drupal\harvest\HarvestService;
use Drupal\metastore\MetastoreService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\datastore\PostImportResultFactory;

/**
 * Datastore Import Dashboard form.
 */
class DashboardForm extends FormBase {
  use StringTranslationTrait;

  /**
   * Harvest service.
   *
   * @var \Drupal\harvest\HarvestService
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
   * @var \Drupal\metastore\MetastoreService
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
   * Date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;

  /**
   * The PostImportResultFactory service.
   *
   * @var \Drupal\datastore\PostImportResultFactory
   */
  protected PostImportResultFactory $postImportResultFactory;

  /**
   * DashboardController constructor.
   *
   * @param \Drupal\harvest\HarvestService $harvestService
   *   Harvest service.
   * @param \Drupal\common\DatasetInfo $datasetInfo
   *   Dataset information service.
   * @param \Drupal\metastore\MetastoreService $metastoreService
   *   Metastore service.
   * @param \Drupal\Core\Pager\PagerManagerInterface $pagerManager
   *   Pager manager service.
   * @param \Drupal\Core\Datetime\DateFormatter $dateFormatter
   *   Date formatter service.
   * @param \Drupal\datastore\PostImportResultFactory $postImportResultFactory
   *   The PostImportResultFactory service..
   */
  public function __construct(
    HarvestService $harvestService,
    DatasetInfo $datasetInfo,
    MetastoreService $metastoreService,
    PagerManagerInterface $pagerManager,
    DateFormatter $dateFormatter,
    PostImportResultFactory $postImportResultFactory
  ) {
    $this->harvest = $harvestService;
    $this->datasetInfo = $datasetInfo;
    $this->metastore = $metastoreService;
    $this->pagerManager = $pagerManager;
    $this->dateFormatter = $dateFormatter;
    $this->itemsPerPage = 10;
    $this->postImportResultFactory = $postImportResultFactory;
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
      $container->get('date.formatter'),
      $container->get('dkan.datastore.post_import_result_factory'),
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
      $harvestLoad = iterator_to_array($this->getHarvestLoadStatus($filters['harvest_id']));
      $datasets = array_keys($harvestLoad);
      $total = count($datasets);
      $currentPage = $this->pagerManager->createPager($total, $this->itemsPerPage)->getCurrentPage();

      $chunks = array_chunk($datasets, $this->itemsPerPage) ?: [[]];
      $datasets = $chunks[$currentPage];
    }
    // If no filter values were supplied, fetch from the list of all dataset
    // UUIDs.
    else {
      $total = $this->metastore->count('dataset', TRUE);
      $currentPage = $this->pagerManager->createPager($total, $this->itemsPerPage)->getCurrentPage();
      $datasets = $this->metastore->getIdentifiers(
        'dataset',
        ($currentPage * $this->itemsPerPage),
        $this->itemsPerPage,
        TRUE
      );
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
    // Fetch the dataset status of all harvests.
    $harvestLoad = iterator_to_array($this->getHarvestLoadStatuses());

    $rows = [];
    // Build dataset rows for each of the supplied dataset UUIDs.
    foreach ($datasets as $datasetId) {
      // Gather dataset information.
      $datasetInfo = $this->datasetInfo->gather($datasetId);
      if (empty($datasetInfo['latest_revision'])) {
        continue;
      }

      // Build a table row using its details and harvest status.
      $datasetRow = $this->buildRevisionRows($datasetInfo, $harvestLoad[$datasetId] ?? 'N/A');
      $rows = array_merge($rows, $datasetRow);
    }

    return $rows;
  }

  /**
   * Fetch the status of all harvests.
   *
   * @return \Generator
   *   Array of all the most recent load statuses for all the datasets for all
   *   the harvests that have been run, keyed by dataset UUID. This can
   *   potentially be a very large array to return by value, which is why it is
   *   structured as a generator.
   */
  protected function getHarvestLoadStatuses(): \Generator {
    foreach ($this->harvest->getAllHarvestIds() as $harvestId) {
      yield from $this->getHarvestLoadStatus($harvestId);
    }
  }

  /**
   * Fetch the status of loaded datasets for the most recent harvest run.
   *
   * @param string|null $harvestId
   *   Harvest ID to search for.
   *
   * @return \Generator
   *   Array of harvest load statuses, keyed by dataset UUIDs.
   */
  protected function getHarvestLoadStatus(?string $harvestId): \Generator {
    $result = $this->harvest->getHarvestRunResult(
      $harvestId, $this->harvest->getLastHarvestRunId($harvestId)
    );
    yield from $result['status']['load'] ?? [];
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
      $this->t('Post Import'),
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
    foreach ($datasetInfo as $rev) {
      // Filter out distributions whose resources are not csv or tsv.
      $distributions = array_filter($rev['distributions'], function ($v) {
        return !isset($v['mime_type']) || in_array($v['mime_type'], DataResource::IMPORTABLE_FILE_TYPES);
      });

      if (!empty($distributions)) {
        // For first distribution, combine with revision information.
        $rows[] = array_merge(
          $this->buildRevisionRow($rev, count($distributions), $harvestStatus),
          $this->buildResourcesRow(array_shift($distributions))
        );
      }
      // If there are more distributions, add additional rows for them.
      while (!empty($distributions)) {
        $rows[] = $this->buildResourcesRow(array_shift($distributions));
      }
    }

    return $rows;
  }

  /**
   * Create the three-column row for revision information.
   *
   * @param array $rev
   *   Revision information from DatasetInfo arrray.
   * @param int $resourceCount
   *   Number of resources attached to this dataset revision.
   * @param string $harvestStatus
   *   Dataset harvest status.
   *
   * @return array
   *   Three-column revision row (expected to be merged with one resource row).
   */
  protected function buildRevisionRow(array $rev, int $resourceCount, string $harvestStatus) {
    // Moderation state can be 'hidden', which is not a good CSS class if we
    // don't want data to be hidden. We hijack the 'registered' class for use
    // here.
    $moderation_class = $rev['moderation_state'];
    if ($moderation_class == 'hidden') {
      $moderation_class = 'published-hidden';
    }
    return [
      [
        'rowspan' => $resourceCount,
        'data' => [
          '#theme' => 'datastore_dashboard_dataset_cell',
          '#uuid' => $rev['uuid'],
          '#title' => $rev['title'],
          '#url' => Url::fromUri("internal:/dataset/$rev[uuid]"),
        ],
      ],
      [
        'rowspan' => $resourceCount,
        'class' => $moderation_class,
        'data' => [
          '#theme' => 'datastore_dashboard_revision_cell',
          '#revision_id' => $rev['revision_id'],
          '#modified' => $this->dateFormatter->format(strtotime($rev['modified_date_dkan']), 'short'),
          '#moderation_state' => $rev['moderation_state'],
        ],
      ],
      [
        'rowspan' => $resourceCount,
        'data' => $harvestStatus,
        'class' => strtolower($harvestStatus),
      ],
    ];
  }

  /**
   * Build resources table using the supplied distributions.
   *
   * @param array|string $dist
   *   Distribution details.
   *
   * @return array
   *   Distribution table render array.
   */
  protected function buildResourcesRow($dist): array {
    if (is_array($dist) && isset($dist['distribution_uuid'])) {
      $postImportResult = $this->postImportResultFactory->initializeFromDistribution($dist);
      $postImportInfo = $postImportResult->retrieveJobStatus();
      $status = $postImportInfo ? $postImportInfo['post_import_status'] : "waiting";
      $error = $postImportInfo ? $postImportInfo['post_import_error'] : NULL;

      return [
        [
          'data' => [
            '#theme' => 'datastore_dashboard_resource_cell',
            '#uuid' => $dist['distribution_uuid'],
            '#file_name' => basename($dist['source_path']),
            '#file_path' => UrlHostTokenResolver::resolve($dist['source_path']),
          ],
        ],
        $this->buildStatusCell($dist['fetcher_status']),
        $this->buildStatusCell($dist['importer_status'], $dist['importer_percent_done'], $this->cleanUpError($dist['importer_error'])),
        $this->buildPostImportStatusCell($status, $error),
      ];
    }
    return ['', '', '', ''];
  }

  /**
   * Create a cell for a job status.
   *
   * @param string $status
   *   Current job status.
   * @param int|null $percentDone
   *   Percent done, 0-100.
   * @param null|string $error
   *   An error message, if any.
   *
   * @return array
   *   Renderable array.
   */
  protected function buildStatusCell(string $status, ?int $percentDone = NULL, ?string $error = NULL) {
    return [
      'data' => [
        '#theme' => 'datastore_dashboard_status_cell',
        '#status' => $status,
        '#percent' => $percentDone ?? NULL,
        '#error' => $error,
      ],
      'class' => str_replace('_', '-', $status),
    ];
  }

  /**
   * Create a cell for a post import job status.
   *
   * @param string $status
   *   Current job status.
   * @param null|string $error
   *   An error message, if any.
   *
   * @return array
   *   Renderable array.
   */
  protected function buildPostImportStatusCell(string $status, ?string $error = NULL) {
    return [
      'data' => [
        '#theme' => 'datastore_dashboard_post_import_status_cell',
        '#status' => $status,
        '#error' => $error,
      ],
      'class' => str_replace('_', '-', $status),
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
  private function cleanUpError(mixed $error) {
    $error = (string) $error;
    $mysqlErrorPattern = '/^SQLSTATE\[[A-Z0-9]+\]: .+?: [0-9]+ (.+?): [A-Z]/';
    if (preg_match($mysqlErrorPattern, $error, $matches)) {
      return $matches[1];
    }
    return $error;
  }

}
