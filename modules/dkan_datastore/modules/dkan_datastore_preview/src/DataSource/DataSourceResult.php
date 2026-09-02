<?php

namespace Drupal\dkan_datastore_preview\DataSource;

/**
 * Value object holding one page of data source results.
 */
final class DataSourceResult {

  /**
   * Constructor.
   *
   * @param array $rows
   *   Row objects (\stdClass) keyed by column machine name.
   * @param int $totalCount
   *   Total number of rows matching the conditions, ignoring limit/offset.
   */
  public function __construct(
    public readonly array $rows,
    public readonly int $totalCount,
  ) {}

}
