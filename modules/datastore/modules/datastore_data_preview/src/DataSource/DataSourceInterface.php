<?php

namespace Drupal\datastore_data_preview\DataSource;

/**
 * Interface for data sources that provide tabular data for preview.
 */
interface DataSourceInterface {

  /**
   * The internal record_number field, excluded from preview display.
   */
  const HIDDEN_FIELD = 'record_number';

  /**
   * Fetch data from the source.
   *
   * @param string $resource_id
   *   The resource identifier.
   * @param int $limit
   *   Maximum number of rows to return.
   * @param int $offset
   *   Number of rows to skip.
   * @param string|null $sort_field
   *   Column machine name to sort by, or NULL for default order.
   * @param string $sort_direction
   *   Sort direction: 'asc' or 'desc'.
   * @param array $conditions
   *   Filter conditions in DKAN Query format:
   *   [['property' => 'col', 'value' => 'val', 'operator' => '='], ...].
   * @param array $properties
   *   Column machine names to retrieve. Empty array means all columns.
   *
   * @return \Drupal\datastore_data_preview\DataSource\DataSourceResult
   *   The query result.
   */
  public function fetchData(
    string $resource_id,
    int $limit,
    int $offset,
    ?string $sort_field,
    string $sort_direction,
    array $conditions = [],
    array $properties = [],
  ): DataSourceResult;

}
