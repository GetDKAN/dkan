<?php

namespace Drupal\dkan_datastore_preview;

use Drupal\dkan_datastore_preview\DataSource\DataSourceInterface;

/**
 * Builds render arrays for data preview tables.
 */
interface DataPreviewBuilderInterface {

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
  public function build(DataSourceInterface $dataSource, string $resource_id, array $options = []): ?array;

}
