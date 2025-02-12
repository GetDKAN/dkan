<?php

namespace Drupal\common\Commands;

use Drupal\common\DatasetInfo;
use Drush\Commands\DrushCommands;
use Drupal\Core\Database\Connection;

/**
 * Drush commands providing utility common to DKAN's sub-modules.
 */
class CommonCommands extends DrushCommands {

  /**
   * Dataset information service.
   *
   * @var \Drupal\common\DatasetInfo
   */
  protected $datasetInfo;

  /**
   * Database connection service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * CommonCommands constructor.
   *
   * @param \Drupal\common\DatasetInfo $datasetInfo
   *   Dataset information service.
   * @param \Drupal\Core\Database\Connection $database
   *   Database connection service.
   */
  public function __construct(DatasetInfo $datasetInfo, Connection $database) {
    parent::__construct();
    $this->datasetInfo = $datasetInfo;
    $this->database = $database;
  }

  /**
   * Display information about a dataset and its resource(s).
   *
   * @param string $uuid
   *   A dataset identifier.
   *
   * @usage dkan:dataset-info abcd-1234
   *   Display info about dataset abcd-1234 and its resource(s).
   *
   * @command dkan:dataset-info
   */
  public function datasetInfo(string $uuid) {
    return json_encode($this->datasetInfo->gather($uuid), JSON_PRETTY_PRINT);
  }

  /**
   * Return the resource ID associated with the provided data table name.
   *
   * @param string $data_table_name
   *   Data Table name, e.g., "datastore_8b7a21d442d603b113f1a17beac8bcdd".
   *
   * @command dkan:datastore:lookup-resource
   */
  public function datatableToResourceLookup(string $data_table_name) {
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
        echo "Resource lookup: Can not map data table name " . $data_table_name . " to resource ID." . PHP_EOL;
      }
    }
  }

  /**
   * Return the distribution associated with the provided resource ID.
   *
   * @param string $resource_id
   *   Resource ID, e.g., "6e5a8b0e5f9ae95d1e239844aaab2db4".
   *
   * @command dkan:datastore:lookup-distribution
   */
  public function resourceToDistribution(string $resource_id) {
    if ($resource_id) {
      // Tack on an underscore to the
      // end of the provided resource ID.
      // We need this to search the json metadata table
      // for the identifier property correctly without
      // bringing back too many results.
      // (An underscore always follows the complete resource ID).
      $search_id = $resource_id . '_';
      // Now we have our associated resource identifier so
      // Use it to find the associated distribution UUID
      // from the node__field_json_metadata table.
      // Build the query for searching the json metadata table.
      $distribution_query = $this->database->select('node__field_json_metadata', 'nfm');
      // Add our JSON_EXTRACT expression
      // targeting the identifier property.
      $distribution_query->addExpression("JSON_UNQUOTE(JSON_EXTRACT(nfm.field_json_metadata_value, '$.identifier'))", 'identifier');
      // Add a LIKE condition with our
      // escaped search value.
      $distribution_query->condition(
        'nfm.field_json_metadata_value',
        '%' . $this->database->escapeLike($search_id) . '%',
        'LIKE'
      );
      // Get our result (distribution UUID) from our
      // executed query as an associative array.
      $distribution_result = $distribution_query->execute()->fetchAll(\PDO::FETCH_ASSOC);
      // Extract the distribution identifier value
      // from the associative array.
      // This should only be one level deep.
      if ($distribution_result) {
        $distribution_identifier = $distribution_result[0]['identifier'];
        return $distribution_identifier;
      }
      else {
        return "Distribution lookup: Can not map resource ID " . $resource_id . " to distribution UUID." . PHP_EOL;
      }
    }
  }

  /**
   * Return the dataset UUID associated with the provided distribution UUID.
   *
   * @param string $distribution_uuid
   *   Distribution ID, e.g., "d10163be-b7cc-5f76-a5e7-8d2bb4cda6bc".
   *
   * @command dkan:datastore:lookup-dataset
   */
  public function distributionToDataset(string $distribution_uuid) {
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
        return "Dataset lookup: Cannot map distribution UUID " . $distribution_uuid . " to dataset UUID.";
      }
    }
    return "Dataset lookup: Distribution UUID needs to be 36 characters.";
  }

  /**
   * Return the dataset uuid associated with the provided data table name.
   *
   * Will do the following:
   * - Deconstruct the data table id.
   * -- datastore_
   * -- identifier
   * -- version
   * -- perspective
   * - Lookup the associated resource ID
   * - Lookup the associated distribution UUID
   * - Lookup the associated dataset UUID
   * - Display dataset UUID to console.
   *
   * @param string $data_table_name
   *   Data Table name, e.g., "datastore_8b7a21d442d603b113f1a17beac8bcdd".
   *
   * @command dkan:datastore:reverse-dataset-lookup
   */
  public function reverseDatasetLookup(string $data_table_name) {
    $resource_id = '';
    $distribution_uuid = '';
    if ($data_table_name) {
      $resource_id = $this->datatableToResourceLookup($data_table_name);
    }
    if ($resource_id) {
      $distribution_uuid = $this->resourceToDistribution($resource_id);
    }
    if ($distribution_uuid) {
      $dataset_uuid = $this->distributionToDataset($distribution_uuid);
      // Output to console and end command.
      $this->output()->writeln('Dataset UUID = ' . $dataset_uuid);
      return DrushCommands::EXIT_SUCCESS;
    }
    $this->output()->writeln('Can not map data table to dataset: ' . $data_table_name);
    return DrushCommands::EXIT_FAILURE;
  }

}
