<?php

namespace Drupal\dkan_datastore_preview\Service;

use Drupal\Component\Utility\Html;
use Drupal\Core\Pager\PagerManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\dkan_datastore_preview\DataSource\DataSourceInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Builds render arrays for data preview tables.
 *
 * Sorting and page size use query parameters prefixed per table instance
 * (option 'query_prefix', e.g. "dp0_order") so several previews can coexist
 * on one page without sharing state. Core TableSort is not used because its
 * "order"/"sort" parameter names cannot be prefixed. Paging uses core's
 * native comma-separated multi-pager "page" parameter via 'pager_element'.
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
   * @param \Drupal\dkan_datastore_preview\DataSource\DataSourceInterface $dataSource
   *   The data source to query.
   * @param string $resource_id
   *   The resource identifier ("identifier__version").
   * @param array $options
   *   Options array with keys:
   *   - columns (string[]): Column machine names to display (empty = all).
   *   - page_sizes (int[]): Available page sizes.
   *   - default_page_size (int): Default page size.
   *   - default_sort (string|null): Default sort column machine name.
   *   - default_sort_direction (string): 'asc' or 'desc'.
   *   - conditions (array): Filter conditions.
   *   - pager_element (int): Pager element index, unique per table on a page.
   *   - query_prefix (string): Prefix for this table's query parameters,
   *     unique per table on a page (e.g. "dp0_").
   *   - caption (string|null): Table caption.
   *
   * @return array|null
   *   Render array with #theme => 'dkan_datastore_preview', or NULL when the
   *   resource has no queryable table yet.
   */
  public function build(DataSourceInterface $dataSource, string $resource_id, array $options = []): ?array {
    $options += [
      'columns' => [],
      'page_sizes' => self::DEFAULT_PAGE_SIZES,
      'default_page_size' => 25,
      'default_sort' => NULL,
      'default_sort_direction' => 'asc',
      'conditions' => [],
      'pager_element' => 0,
      'query_prefix' => '',
      'caption' => NULL,
    ];

    $schema = $dataSource->getSchema($resource_id);
    if (empty($schema['fields'])) {
      return NULL;
    }

    $request = $this->requestStack->getCurrentRequest() ?? Request::create('/');
    $prefix = $options['query_prefix'];

    $pageSizeParam = $prefix . 'page_size';
    $pageSize = (int) $request->query->get($pageSizeParam, $options['default_page_size']);
    if (!in_array($pageSize, $options['page_sizes'])) {
      $pageSize = $options['default_page_size'];
    }

    $displayFields = $this->getDisplayFields($schema, $options['columns']);
    $sort = $this->getSortContext($displayFields, $options, $request);

    $currentPage = $this->getCurrentPage($request, $options['pager_element']);
    $offset = $currentPage * $pageSize;

    $result = $dataSource->fetchData(
      $resource_id,
      $pageSize,
      $offset,
      $sort['field'],
      $sort['direction'],
      $options['conditions'],
      $options['columns'],
    );

    // Clamp an out-of-range page (hand-edited URL) to the last valid page.
    if ($result->totalCount > 0 && $offset >= $result->totalCount) {
      $currentPage = (int) ceil($result->totalCount / $pageSize) - 1;
      $offset = $currentPage * $pageSize;
      $result = $dataSource->fetchData(
        $resource_id,
        $pageSize,
        $offset,
        $sort['field'],
        $sort['direction'],
        $options['conditions'],
        $options['columns'],
      );
    }

    $this->pagerManager->createPager($result->totalCount, $pageSize, $options['pager_element']);

    return [
      '#theme' => 'dkan_datastore_preview',
      '#cache' => [
        'contexts' => [
          'url.query_args:' . $prefix . 'order',
          'url.query_args:' . $prefix . 'sort',
          'url.query_args:' . $pageSizeParam,
          'url.query_args:page',
        ],
      ],
      '#table' => [
        '#type' => 'table',
        '#caption' => $options['caption'],
        '#header' => $this->buildHeader($schema, $displayFields, $sort, $options, $request),
        '#rows' => $this->buildRows($result->rows, $displayFields),
        '#sticky' => FALSE,
        '#empty' => $this->t('No data available.'),
        '#attributes' => ['class' => ['dkan-datastore-preview__table']],
      ],
      '#pager' => [
        '#type' => 'pager',
        '#element' => $options['pager_element'],
        '#parameters' => [$pageSizeParam => $pageSize],
      ],
      '#page_size_form' => $this->buildPageSizeForm($options, $pageSize, $request),
      '#result_summary' => $this->buildResultSummary($offset, count($result->rows), $result->totalCount),
      '#attached' => [
        'library' => ['dkan_datastore_preview/preview'],
      ],
    ];
  }

  /**
   * Get the ordered list of displayable field machine names.
   */
  protected function getDisplayFields(array $schema, array $columns): array {
    $fields = array_keys($schema['fields'] ?? []);
    return array_values(array_filter($fields, function ($name) use ($columns) {
      if ($name === DataSourceInterface::HIDDEN_FIELD) {
        return FALSE;
      }
      return empty($columns) || in_array($name, $columns);
    }));
  }

  /**
   * Resolve the active sort from prefixed query parameters.
   *
   * Values are validated against the schema's field names before being used,
   * so arbitrary query input never reaches the data source.
   *
   * @return array
   *   ['field' => string|null, 'direction' => 'asc'|'desc'].
   */
  protected function getSortContext(array $displayFields, array $options, Request $request): array {
    $prefix = $options['query_prefix'];
    $orderParam = (string) $request->query->get($prefix . 'order', '');
    $directionParam = strtolower((string) $request->query->get($prefix . 'sort', ''));

    if ($orderParam !== '' && in_array($orderParam, $displayFields, TRUE)) {
      return [
        'field' => $orderParam,
        'direction' => in_array($directionParam, ['asc', 'desc'], TRUE) ? $directionParam : 'asc',
      ];
    }

    $default = $options['default_sort'];
    if ($default && in_array($default, $displayFields, TRUE)) {
      return [
        'field' => $default,
        'direction' => strtolower($options['default_sort_direction']) === 'desc' ? 'desc' : 'asc',
      ];
    }

    return ['field' => NULL, 'direction' => 'asc'];
  }

  /**
   * Build sortable table headers with per-table prefixed sort links.
   */
  protected function buildHeader(array $schema, array $displayFields, array $sort, array $options, Request $request): array {
    $prefix = $options['query_prefix'];
    $header = [];

    foreach ($displayFields as $machineName) {
      $label = $schema['fields'][$machineName]['description'] ?? $machineName;
      $active = ($sort['field'] === $machineName);
      $nextDirection = ($active && $sort['direction'] === 'asc') ? 'desc' : 'asc';

      $query = $request->query->all();
      $query[$prefix . 'order'] = $machineName;
      $query[$prefix . 'sort'] = $nextDirection;
      $this->resetPageQueryElement($query, $options['pager_element']);

      $cellData = [
        'link' => [
          '#type' => 'link',
          '#title' => $label,
          '#url' => $this->currentPathUrl($request, $query),
          '#attributes' => [
            'aria-label' => $this->t('Sort by @label, @direction', [
              '@label' => $label,
              '@direction' => $nextDirection === 'asc' ? $this->t('ascending') : $this->t('descending'),
            ]),
          ],
        ],
      ];

      $cell = ['data' => $cellData];
      if ($active) {
        $cellData['indicator'] = [
          '#theme' => 'tablesort_indicator',
          '#style' => $sort['direction'],
        ];
        $cell = [
          'data' => $cellData,
          'aria-sort' => $sort['direction'] === 'asc' ? 'ascending' : 'descending',
          'class' => ['is-active'],
        ];
      }
      $header[] = $cell;
    }

    return $header;
  }

  /**
   * Get an unrouted URL for the current path with the given query.
   *
   * Path-based rather than '<current>' route-based so previews also render
   * outside a routed request (drush, queues, mail).
   */
  protected function currentPathUrl(Request $request, array $query): Url {
    return Url::fromUri('base:' . ltrim($request->getPathInfo(), '/'), ['query' => $query]);
  }

  /**
   * Build table rows from result data.
   */
  protected function buildRows(array $rows, array $displayFields): array {
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
   * Get the current page number for this table's pager element.
   */
  protected function getCurrentPage(Request $request, int $pagerElement): int {
    $pageParam = (string) $request->query->get('page', '');
    if ($pageParam === '') {
      return 0;
    }
    // Drupal uses comma-separated page numbers for multiple pagers.
    $pages = explode(',', $pageParam);
    return max(0, (int) ($pages[$pagerElement] ?? 0));
  }

  /**
   * Reset this table's position in the multi-pager "page" query parameter.
   *
   * Other tables' page positions are preserved.
   */
  protected function resetPageQueryElement(array &$query, int $pagerElement): void {
    if (!isset($query['page']) || !is_scalar($query['page'])) {
      return;
    }
    $pages = explode(',', (string) $query['page']);
    $pages = array_pad($pages, $pagerElement + 1, '0');
    $pages[$pagerElement] = '0';
    if (array_unique($pages) === ['0']) {
      unset($query['page']);
      return;
    }
    $query['page'] = implode(',', $pages);
  }

  /**
   * Build the variables for the page size GET form.
   *
   * Rendered by the dkan-datastore-preview template as a real GET form so it
   * works without JavaScript. Foreign query parameters (other tables' state)
   * are carried as hidden inputs.
   */
  protected function buildPageSizeForm(array $options, int $currentSize, Request $request): array {
    $paramName = $options['query_prefix'] . 'page_size';

    $hidden = [];
    foreach ($request->query->all() as $name => $value) {
      if ($name === $paramName || !is_scalar($value)) {
        continue;
      }
      $hidden[$name] = (string) $value;
    }
    $this->resetPageQueryElement($hidden, $options['pager_element']);

    return [
      'action' => $request->getPathInfo(),
      'id' => Html::getId('dkan-datastore-preview-' . $options['query_prefix'] . 'page-size'),
      'param_name' => $paramName,
      'sizes' => $options['page_sizes'],
      'current' => $currentSize,
      'hidden' => $hidden,
    ];
  }

  /**
   * Build the result summary markup.
   */
  protected function buildResultSummary(int $offset, int $rowCount, int $totalCount): array {
    if ($totalCount === 0) {
      return [
        '#type' => 'html_tag',
        '#tag' => 'span',
        '#value' => $this->t('No results'),
        '#attributes' => ['class' => ['dkan-datastore-preview__summary']],
      ];
    }

    $start = $offset + 1;
    $end = $offset + $rowCount;

    return [
      '#type' => 'html_tag',
      '#tag' => 'span',
      '#value' => $this->t('Showing @start-@end of @total results', [
        '@start' => number_format($start),
        '@end' => number_format($end),
        '@total' => number_format($totalCount),
      ]),
      '#attributes' => ['class' => ['dkan-datastore-preview__summary']],
    ];
  }

}
