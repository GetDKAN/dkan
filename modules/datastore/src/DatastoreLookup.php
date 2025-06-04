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
   * {@inheritDoc}
   */
  public function distributionToDataset(string $distribution_uuid): string {
    if ($distribution_uuid && strlen($distribution_uuid) == 36) {
      // Now we have the distribution identifier,
      // so lets get the dataset UUID
      // Associated with it from
      // the node__field_json_metadata table.
      $dataset_query = $this->database->select('node__field_json_metadata', 'nfm');
      // Add JSON_EXTRACT to get the identifier.
      $dataset_query->addExpression("JSON_UNQUOTE(JSON_EXTRACT(nfm.field_json_metadata_value, '$.identifier'))", 'identifier');
      // Add condition to check if the
      // column contains the distribution UUID.
      $dataset_query->condition(
          'nfm.field_json_metadata_value',
          '%' . $this->database->escapeLike($distribution_uuid) . '%',
          'LIKE'
      );
      // Execute query.
      $dataset_result = $dataset_query->execute()->fetchAll(\PDO::FETCH_ASSOC);
      // Save the second item in the array to be the dataset identifier
      // as this identifier is the dataset UUID related to the distribution
      // which appears in the row that holds the distribution and dataset.
      // The other is the row that holds the distribution and resource.
      if ($dataset_result) {
        $dataset_identifier = $dataset_result[1]['identifier'];
        return $dataset_identifier;
      }
      else {
        throw new \Exception("Dataset lookup: Cannot map distribution UUID {$distribution_uuid} to dataset UUID. Please make sure the expected distribution exists in the daatabase.");
      }
    }
    throw new \Exception("Dataset lookup: Distribution UUID needs to be 36 characters.");
  }

}
