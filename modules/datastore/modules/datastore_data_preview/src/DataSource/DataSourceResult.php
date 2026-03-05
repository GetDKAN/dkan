<?php

namespace Drupal\datastore_data_preview\DataSource;

/**
 * Read-only DTO for data source query results.
 */
class DataSourceResult {

  /**
   * Constructor.
   *
   * @param array $rows
   *   Array of result row objects (stdClass).
   * @param int $totalCount
   *   Total number of matching rows (ignoring limit/offset).
   * @param array $schema
   *   Schema with 'fields' key in Drupal schema format.
   */
  public function __construct(
    public readonly array $rows,
    public readonly int $totalCount,
    public readonly array $schema,
  ) {}

}
