<?php

declare(strict_types=1);

namespace Drupal\datastore;

use Drupal\Core\Database\Connection;
use Drupal\metastore\Reference\ReferenceLookup;

/**
 * Implementation of various lookup utilities related to the datastore.
 */
class DatastoreLookup implements DatastoreLookupInterface {

  /**
   * Database connection service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Reference lookup service.
   *
   * @var \Drupal\metastore\Reference\ReferenceLookup
   */

  protected $referenceLookup;

  /**
   * DataStoreLookupService constructor.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   Database connection service.
   */
  public function __construct(Connection $database, ReferenceLookup $referenceLookup) {
    $this->database = $database;
    $this->referenceLookup = $referenceLookup;
  }

  /**
   * {@inheritDoc}
   */
  public function datatableToResourceLookup(string $data_table_name): string {
    if ($data_table_name) {
      // Establish DB connection.
      $resource_query = $this->database->select('dkan_metastore_resource_mapper', 'dm')
        ->fields('dm', ['identifier']);
      // Add the condition using a raw SQL expression.
      // We want just the identifier here which
      // is part of an amalgamation of an MD5 hash of the
      // identifier, version, and perspective
      // of the related resource which
      // in turn creates the data table name,
      // so we're reversing that with this query.
      $resource_query->where(
        'CONCAT(\'datastore_\', MD5(CONCAT(identifier, \'__\', version, \'__\', perspective))) = :data_table_name',
        [':data_table_name' => $data_table_name]
      );
      // Execute the query and fetch the results as an associative array.
      $resource_result = $resource_query->execute()->fetchAll(\PDO::FETCH_ASSOC);
      // If our query returns something...
      // Extract the identifier value
      // from the returned associative array.
      if ($resource_result) {
        $resource_identifier = $resource_result[0]['identifier'];
        return $resource_identifier;
      }
      else {
        throw new \Exception("Resource lookup: Can not map data table name {$data_table_name} to resource ID. Please make sure your data table name exists as a table in the database.");
      }
    }
  }

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
  public function resourceToDistribution(string $resource_id): string {
    $referencers = $this->referenceLookup->getReferencers($resource_id, 'dcat:distribution');

    if (empty($referencers)) {
      throw new \RuntimeException("Distribution lookup: Can not map resource ID {$resource_id} to distribution UUID. Please make sure your resource exists in the database.");
    }

    return $referencers[0];
  }

  /**
   * Get the dataset UUID for a given distribution UUID.
   *
   * @param string $distribution_id
   *   The UUID of the distribution node.
   *
   * @return string
   *   The UUID of the dataset node.
   *
   * @throws \RuntimeException
   *   If no dataset is found.
   */
  public function distributionToDataset(string $distribution_id): string {
    if (strlen($distribution_id) !== 36) {
      throw new \InvalidArgumentException("Dataset lookup: Distribution UUID must be 36 characters.");
    }

    $referencers = $this->referenceLookup->getReferencers($distribution_id, 'dcat:dataset');

    if (empty($referencers)) {
      throw new \RuntimeException("No dataset found for distribution ID: {$distribution_id}");
    }

    return $referencers[0];
  }



}
