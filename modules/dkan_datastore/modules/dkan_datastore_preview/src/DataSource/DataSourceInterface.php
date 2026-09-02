<?php

namespace Drupal\dkan_datastore_preview\DataSource;

/**
 * Contract for data preview sources.
 *
 * A data source resolves a resource identifier to tabular data. The MVP ships
 * a single database implementation; this interface is the seam for a future
 * plugin-based data source system.
 */
interface DataSourceInterface {

  /**
   * Internal datastore column that must never be displayed.
   */
  const HIDDEN_FIELD = 'record_number';

  /**
   * Get the Drupal Schema API description of the resource's table.
   *
   * @param string $resource_id
   *   Resource identifier, in "identifier__version" format.
   *
   * @return array
   *   Schema array with a 'fields' key mapping machine names to field info
   *   ('type', 'description'). MUST return an empty array when the resource
   *   has no queryable table (not yet imported, unknown, etc.) and MUST NOT
   *   include self::HIDDEN_FIELD.
   */
  public function getSchema(string $resource_id): array;

  /**
   * Fetch a page of data from the source.
   *
   * @param string $resource_id
   *   Resource identifier, in "identifier__version" format.
   * @param int $limit
   *   Maximum number of rows to return.
   * @param int $offset
   *   Number of rows to skip.
   * @param string|null $sort_field
   *   Machine name of the column to sort by, or NULL for storage order.
   * @param string $sort_direction
   *   Either 'asc' or 'desc'.
   * @param array $conditions
   *   Filter conditions; each an array with 'property', 'value' and an
   *   optional 'operator' (defaults to '=').
   * @param array $properties
   *   Machine names of columns to return (empty = all).
   *
   * @return \Drupal\dkan_datastore_preview\DataSource\DataSourceResult
   *   The rows and total count.
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
