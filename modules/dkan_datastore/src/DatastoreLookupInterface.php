<?php

declare(strict_types=1);

namespace Drupal\dkan_datastore;

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
   * @throws \RuntimeException
   *   If $table_name can not be found in DB.
   */
  public function tableToResourceLookup(string $table_name): string;

  /**
   * Get the distribution UUID for a given resource ID.
   *
   * @param string $resource_id
   *   The UUID of the resource node.
   *
   * @return string
   *   The UUID of the related distribution node.
   *
   * @throws \RuntimeException
   *   If no distribution is found.
   */
  public function resourceToDistribution(string $resource_id): string;

  /**
   * Get the dataset UUID for a given resource ID.
   *
   * This will only work if distributions are not separate referenced entities,
   * and downloadURL values are stored directly in datasets.
   *
   * @param string $resource_id
   *   The UUID of the resource node.
   *
   * @return string
   *   The UUID of the related dataset node.
   *
   * @throws \RuntimeException
   *   If no dataset is found.
   */
  public function resourceToDataset(string $resource_id): string;

  /**
   * Get the dataset UUID for a given distribution UUID.
   *
   * @param string $distribution_uuid
   *   The UUID of the distribution node.
   *
   * @return string
   *   The UUID of the dataset node.
   *
   * @throws \RuntimeException
   *   If no dataset is found.
   */
  public function distributionToDataset(string $distribution_uuid): string;

}
