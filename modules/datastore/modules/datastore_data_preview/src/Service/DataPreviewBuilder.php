<?php

namespace Drupal\datastore_data_preview\Service;

use Drupal\Core\Pager\PagerManagerInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Utility\TableSort;
use Drupal\datastore_data_preview\DataSource\DataSourceInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Builds render arrays for data preview tables.
 */
class DataPreviewBuilder {

  use StringTranslationTrait;

  /**
   * Default page size options.
   */
  const DEFAULT_PAGE_SIZES = [10, 25, 50, 100];

  /**
   * Constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack.
   * @param \Drupal\Core\Pager\PagerManagerInterface $pagerManager
   *   The pager manager.
   */
  public function __construct(
    protected RequestStack $requestStack,
    protected PagerManagerInterface $pagerManager,
  ) {}

  /**
   * Build a data preview render array.
   *
   * @param \Drupal\datastore_data_preview\DataSource\DataSourceInterface $dataSource
   *   The data source to query.
   * @param string $resource_id
   *   The resource identifier.
   * @param array $options
   *   Options array with keys:
   *   - columns (string[]): Column machine names to display (empty = all).
   *   - page_sizes (int[]): Available page sizes.
   *   - default_page_size (int): Default page size.
   *   - default_sort (string|null): Default sort column machine name.
   *   - default_sort_direction (string): 'asc' or 'desc'.
   *   - conditions (array): Filter conditions.
   *   - pager_element (int): Pager element ID.
   *   - query_prefix (string): Prefix for page_size query param.
   *
   * @return array
   *   Render array with #theme => 'datastore_data_preview'.
   */
  public function build(DataSourceInterface $dataSource, string $resource_id, array $options = []): array {
    $options += [
      'columns' => [],
      'page_sizes' => self::DEFAULT_PAGE_SIZES,
      'default_page_size' => 25,
      'default_sort' => NULL,
      'default_sort_direction' => 'asc',
      'conditions' => [],
      'pager_element' => 0,
      'query_prefix' => '',
    ];

    $request = $this->requestStack->getCurrentRequest();
    $pageSizeParam = $options['query_prefix'] . 'page_size';
    $pageSize = (int) $request->query->get($pageSizeParam, $options['default_page_size']);

    // Validate page size.
    if (!in_array($pageSize, $options['page_sizes'])) {
      $pageSize = $options['default_page_size'];
    }

    // Fetch schema with a probe query.
    $probeResult = $dataSource->fetchData($resource_id, 1, 0, NULL, 'asc', $options['conditions'], []);
    $schema = $probeResult->schema;

    // Build table headers from schema.
    $header = $this->buildHeader($schema, $options);

    // Resolve current sort using Drupal's TableSort.
    $context = TableSort::getContextFromRequest($header, $request);
    $sortField = $context['sql'];
    $sortDirection = $context['sort'];

    // Resolve current page.
    $currentPage = $this->getCurrentPage($request, $options['pager_element']);

    $offset = $currentPage * $pageSize;

    // Fetch the actual data.
    $result = $dataSource->fetchData(
      $resource_id,
      $pageSize,
      $offset,
      $sortField,
      $sortDirection,
      $options['conditions'],
      $options['columns'],
    );

    // Initialize the pager.
    $this->pagerManager->createPager($result->totalCount, $pageSize, $options['pager_element']);

    // Build table rows.
    $rows = $this->buildRows($result->rows, $schema, $options['columns']);

    // Build page size selector.
    $pageSizeForm = $this->buildPageSizeSelector($options['page_sizes'], $pageSize, $pageSizeParam, $request);

    // Build result summary.
    $resultSummary = $this->buildResultSummary($offset, count($result->rows), $result->totalCount);

    return [
      '#theme' => 'datastore_data_preview',
      '#table' => [
        '#type' => 'table',
        '#header' => $header,
        '#rows' => $rows,
        '#empty' => $this->t('No data available.'),
        '#attributes' => ['class' => ['data-preview-table']],
      ],
      '#pager' => [
        '#type' => 'pager',
        '#element' => $options['pager_element'],
      ],
      '#page_size_form' => $pageSizeForm,
      '#result_summary' => $resultSummary,
      '#attached' => [
        'library' => ['datastore_data_preview/data_preview'],
      ],
    ];
  }

  /**
   * Build sortable table headers from schema.
   */
  protected function buildHeader(array $schema, array $options): array {
    $header = [];
    $fields = $schema['fields'] ?? [];

    foreach ($fields as $machineName => $field) {
      if ($machineName === DataSourceInterface::HIDDEN_FIELD) {
        continue;
      }
      // If columns are specified, only include those.
      if (!empty($options['columns']) && !in_array($machineName, $options['columns'])) {
        continue;
      }

      $label = $field['description'] ?? $machineName;
      $headerItem = [
        'data' => $label,
        'field' => $machineName,
      ];

      // Mark the default sort column.
      if ($options['default_sort'] === $machineName) {
        $headerItem['sort'] = $options['default_sort_direction'];
      }

      $header[] = $headerItem;
    }

    return $header;
  }

  /**
   * Build table rows from result data.
   */
  protected function buildRows(array $rows, array $schema, array $columns): array {
    $fields = $schema['fields'] ?? [];
    $fieldNames = array_keys($fields);

    // Filter to requested columns, excluding record_number.
    $displayFields = array_filter($fieldNames, function ($name) use ($columns) {
      if ($name === DataSourceInterface::HIDDEN_FIELD) {
        return FALSE;
      }
      return empty($columns) || in_array($name, $columns);
    });

    $tableRows = [];
    foreach ($rows as $row) {
      $cells = [];
      foreach ($displayFields as $fieldName) {
        $cells[] = $row->{$fieldName} ?? '';
      }
      $tableRows[] = $cells;
    }
    return $tableRows;
  }

  /**
   * Get the current page number from the request.
   */
  protected function getCurrentPage(Request $request, int $pagerElement): int {
    $pageParam = $request->query->get('page', '');
    if ($pageParam === '') {
      return 0;
    }

    // Drupal uses comma-separated page numbers for multiple pagers.
    $pages = explode(',', $pageParam);
    return (int) ($pages[$pagerElement] ?? 0);
  }

  /**
   * Build the page size selector as a dropdown select.
   */
  protected function buildPageSizeSelector(array $pageSizes, int $currentSize, string $paramName, Request $request): array {
    $currentQuery = $request->query->all();
    $basePath = $request->getPathInfo();

    $optionsHtml = '';
    foreach ($pageSizes as $size) {
      $query = $currentQuery;
      $query[$paramName] = $size;
      // Reset to first page when changing page size.
      unset($query['page']);

      $queryString = http_build_query($query);
      $href = $basePath . ($queryString ? '?' . $queryString : '');

      $selected = ($size === $currentSize) ? ' selected' : '';
      $optionsHtml .= '<option value="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '"' . $selected . '>' . $size . '</option>';
    }

    $selectHtml = '<span class="data-preview-page-size-wrapper">'
      . '<span class="data-preview-page-size-label">' . $this->t('Show') . '</span>'
      . '<select class="data-preview-page-size-select">' . $optionsHtml . '</select>'
      . '<span class="data-preview-page-size-label">' . $this->t('entries') . '</span>'
      . '</span>';

    return [
      '#markup' => Markup::create($selectHtml),
    ];
  }

  /**
   * Build the result summary markup.
   */
  protected function buildResultSummary(int $offset, int $rowCount, int $totalCount): array {
    if ($totalCount === 0) {
      return ['#markup' => '<span class="data-preview-summary">' . $this->t('No results') . '</span>'];
    }

    $start = $offset + 1;
    $end = $offset + $rowCount;

    return [
      '#markup' => '<span class="data-preview-summary">' . $this->t('Showing @start-@end of @total results', [
        '@start' => number_format($start),
        '@end' => number_format($end),
        '@total' => number_format($totalCount),
      ]) . '</span>',
    ];
  }

}
