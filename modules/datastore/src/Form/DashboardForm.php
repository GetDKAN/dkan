<?php

namespace Drupal\datastore\Form;

use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Pager\PagerManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\common\DataResource;
use Drupal\common\DatasetInfo;
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
   * Resource type unsupported.
   */
  const RESOURCE_TYPE_UNSUPPORTED = 'unsupported';

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
   */
  protected PostImportResultFactory $postImportResultFactory;

  /**
   * Node storage service.
   */
  protected EntityStorageInterface $nodeStorage;

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
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity type manager service.
   */
  public function __construct(
    HarvestService $harvestService,
    DatasetInfo $datasetInfo,
    MetastoreService $metastoreService,
    PagerManagerInterface $pagerManager,
    DateFormatter $dateFormatter,
    PostImportResultFactory $postImportResultFactory,
    EntityTypeManagerInterface $entityTypeManager,
  ) {
    $this->harvest = $harvestService;
    $this->datasetInfo = $datasetInfo;
    $this->metastore = $metastoreService;
    $this->pagerManager = $pagerManager;
    $this->dateFormatter = $dateFormatter;
    $this->nodeStorage = $entityTypeManager->getStorage('node');
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
      $container->get('entity_type.manager'),
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
        'dataset_title' => [
          '#type' => 'textfield',
          '#weight' => 1,
          '#title' => $this->t('Dataset Title'),
          '#default_value' => $filters['dataset_title'] ?? '',
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
   * Filters over-ride each other, in this order of priority:
   * - UUID
   * - Title search
   * - Harvest plan ID.
   *
   * @param string[] $filters
   *   Datasets filters. Keys determine the filter. Recognized keys:
   *   - uuid - Dataset UUID.
   *   - dataset_title - A CONTAINS search within the dataset title field.
   *   - harvest_id - A harvest plan ID.
   *
   * @return string[]
   *   Paged, filtered list of dataset UUIDs. If no filter was supplied, all
   *   dataset UUIDs will be returned, paged.
   */
  protected function getDatasets(array $filters): array {
    $datasets = [];

    // If a value was supplied for the UUID filter, include only it in the list
    // of dataset UUIDs returned.
    if (isset($filters['uuid'])) {
      $datasets = [$filters['uuid']];
    }
    // Is the user searching for a dataset title?
    elseif (isset($filters['dataset_title'])) {
      $results = $this->getDatasetsByTitle($filters);
      $datasets = $this->pagedFilteredList($results);
    }
    // If a value was supplied for the harvest ID filter, retrieve dataset UUIDs
    // belonging to the specified harvest.
    elseif (isset($filters['harvest_id'])) {
      $harvestLoad = iterator_to_array($this->getHarvestLoadStatus($filters['harvest_id']));
      $datasets = array_keys($harvestLoad);
      $datasets = $this->pagedFilteredList($datasets);
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
   * Entity query for nodes containing the dataset title.
   *
   * @param string[] $filters
   *   Datasets filters.
   *
   * @return string[]
   *   Dataset UUIDs .
   */
  protected function getDatasetsByTitle(array $filters): array {
    $datasets = [];

    // Get the ids using an entity query, because our dataset title is in the
    // node title field.
    // @todo Unify different queries against Data nodes using a repository or
    // the NodeData wrapper.
    $query = $this->nodeStorage->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', 'data')
      ->condition('field_data_type', 'dataset');

    $searchTerms = array_filter(explode(' ', trim($filters['dataset_title'])));

    if (!empty($searchTerms)) {
      $titleGroup = $query->andConditionGroup();
      foreach ($searchTerms as $term) {
        $titleGroup->condition('title', $term, 'CONTAINS');
      }
      $query->condition($titleGroup);
    }

    $results = $query->execute();

    foreach ($this->nodeStorage->loadMultiple($results) as $node) {
      $datasets[] = $node->uuid();
    }

    return $datasets;
  }

  /**
   * Paged, filtered list of dataset UUIDs.
   *
   * @param string[] $datasets
   *   Dataset UUIDs.
   *
   * @return string[]
   *   Paged, filtered list of dataset UUIDs.
   */
  protected function pagedFilteredList(array $datasets): array {
    $total = count($datasets);
    $currentPage = $this->pagerManager->createPager($total, $this->itemsPerPage)->getCurrentPage();
    $chunks = array_chunk($datasets, $this->itemsPerPage) ?: [[]];

    return $chunks[$currentPage];
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
    $result = $this->harvest->getHarvestRunResult($harvestId);
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
      $distributions = $rev['distributions'];
      // For first distribution, combine with revision information.
      $rows[] = array_merge(
        $this->buildRevisionRow($rev, count($distributions), $harvestStatus),
        $this->buildResourcesRow(array_shift($distributions))
      );
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
          '#modified' => $this->dateFormatter->format(strtotime((string) $rev['modified_date_dkan']), 'short'),
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
      $postImportStatus = $postImportInfo ? $postImportInfo['post_import_status'] : "waiting";
      $error = $postImportInfo ? $postImportInfo['post_import_error'] : NULL;
      $mime_type = in_array($dist['mime_type'], DataResource::IMPORTABLE_FILE_TYPES);

      return $this->buildResourceData($dist, $mime_type, $postImportStatus, $error);
    }

    return ['', '', '', ''];
  }

  /**
   * Build resource data array.
   *
   * @param array $dist
   *   Distribution element from a datastore_info array.
   * @param bool $importable
   *   Whether the mime type is importable.
   * @param string $post_import_status
   *   Post import status.
   * @param string|null $error
   *   Error message, if any.
   *
   * @return array
   *   Array of render arrays representing the last three
   *   columns of the dashboard table.
   */
  protected function buildResourceData(array $dist, bool $importable, string $post_import_status, ?string $error): array {
    $data = [
      [
        'data' => [
          '#theme' => 'datastore_dashboard_resource_cell',
          '#uuid' => $dist['distribution_uuid'],
          '#file_name' => basename((string) $dist['source_path']),
          '#file_path' => UrlHostTokenResolver::resolve($dist['source_path']),
        ],
        'class' => $importable ? '' : 'unsupported',
      ],

      $this->buildStatusCell($importable ? $dist['fetcher_status'] : 'unsupported'),
      $importable ? $this->buildStatusCell($dist['importer_status'], $dist['importer_percent_done'], $this->cleanUpError($dist['importer_error'])) : NULL,
      $importable ? $this->buildPostImportStatusCell($post_import_status, $error) : NULL,
    ];

    // Remove empty cells.
    return array_filter($data);
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
    $statusCell = [
      'data' => [
        '#theme' => 'datastore_dashboard_status_cell',
        '#status' => $status === DashboardForm::RESOURCE_TYPE_UNSUPPORTED ? 'Data import is not supported for this resource type' : $status,
        '#percent' => $percentDone ?? NULL,
        '#error' => $error,
      ],
      'class' => str_replace('_', '-', $status),
    ];

    // If the status is unsupported, we want to span the cell across
    // all three columns.
    if ($status === DashboardForm::RESOURCE_TYPE_UNSUPPORTED) {
      $statusCell['colspan'] = 3;
    }

    return $statusCell;
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
