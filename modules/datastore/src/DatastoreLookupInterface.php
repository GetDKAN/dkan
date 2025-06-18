<?php

declare(strict_types=1);

namespace Drupal\datastore;

/**
 * Various lookup utilities related to the datastore.
 */
interface DatastoreLookupInterface {

  /**
   * Return the resource ID associated with the provided datastore table name.
   *
   * @param string $table_name
   *   Data Table name, e.g., "datastore_8b7a21d442d603b113f1a17beac8bcdd".
   *
   * @throws \Exception
   *   If $table_name can not be found in DB.
   */
  public function tableToResourceLookup(string $table_name): string;

  /**
   * Return the distribution associated with the provided resource ID.
   *
   * @param string $resource_id
   *   Resource ID, e.g., "6e5a8b0e5f9ae95d1e239844aaab2db4".
   *
   * @throws \Exception
   *   If $resource_id can not be found in DB.
   */
  public function resourceToDistribution(string $resource_id): string;

  /**
   * Return the dataset UUID associated with the provided distribution UUID.
   *
   * @param string $distribution_uuid
   *   Distribution ID, e.g., "d10163be-b7cc-5f76-a5e7-8d2bb4cda6bc".
   *
   * @throws \Exception
   *   If $distribution_id is not exactly 36 chars.
   */
  public function distributionToDataset(string $distribution_uuid): string;

}
