<?php

namespace Drupal\common\Commands;

use Drupal\common\DatasetInfo;
use Drush\Commands\DrushCommands;
use Drupal\datastore\DatastoreService;
use Drupal\Core\Database\Database;
use Consolidation\OutputFormatters\StructuredData\RowsOfFields;
use Consolidation\OutputFormatters\StructuredData\UnstructuredListData;
use Drupal\Core\StringTranslation\ByteSizeMarkup;

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
   * CommonCommands constructor.
   *
   * @param \Drupal\common\DatasetInfo $datasetInfo
   *   Dataset information service.
   */
  public function __construct(DatasetInfo $datasetInfo) {
    parent::__construct();
    $this->datasetInfo = $datasetInfo;
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
   * Return the dataset uuid associated with
   * the provided data table name.
   *
   * Will do the following:
   * - Deconstruct the data table id.
   * -- datastore_
   * -- identifier
   * -- version
   * -- perspective
   * - Lookup the associated dataset uuid
   * - Display the dataset uuid in the console.
   *
   * @param string $data_table_name
   *   Data Table name, e.g., "datastore_8b7a21d442d603b113f1a17beac8bcdd".
   *
   * @command dkan:datastore:reverse-dataset-lookup
   */
  public function reverseDatasetLookup(string $data_table_name) {
    if ($data_table_name) {
      // Establish DB connection
      $connection = \Drupal::database();
      // Build the query to get the
      // Resource identifier
      $resource_query = $connection->select('dkan_metastore_resource_mapper', 'dm')
        ->fields('dm', ['identifier']);
        // Add the condition using a raw SQL expression.
        // We want just the identifier here which is part of an amalgamation of an MD% hash of the
        // identifier, version, and perspective of the related resource which
        // in turn creates the data table name, so we're reversing that with this query
      $resource_query->where(
        'CONCAT(\'datastore_\', MD5(CONCAT(identifier, \'__\', version, \'__\', perspective))) = :data_table_name',
        [':data_table_name' => $data_table_name]
      );
      // Execute the query and Fetch the results as an associative array.
      $resource_result = $resource_query->execute()->fetchAll(\PDO::FETCH_ASSOC);;
      // If our query returns something
      if($resource_result) {
        // Extract the identifier value from the associative array.
        $resource_identifier = $resource_result[0]['identifier'];
        // Echo for info's sake
        echo 'Associated Resource Identifier: ' . $resource_identifier . PHP_EOL;
        // Now we have our associated resource identifier so
        // Use it to find the associated distribution UUID
        // from the node__field_json_metadata table
        // Set our identifier as our search value for the query
        $search_value = $resource_identifier;
        // Build the query for searching the json metadata table
        $distribution_query = \Drupal::database()->select('node__field_json_metadata', 'nfm');
        // Add our JSON_EXTRACT expression targeting the identifier
        // property
        $distribution_query->addExpression("JSON_UNQUOTE(JSON_EXTRACT(nfm.field_json_metadata_value, '$.identifier'))", 'identifier');
        // Add a LIKE condition with our escaped search value (resource identifier)
        $distribution_query->condition(
          'nfm.field_json_metadata_value',
          '%' . \Drupal::database()->escapeLike($search_value) . '%',
          'LIKE'
        );
        // Get our result (distribution UUID) from our executed query as an associative array.
        $distribution_result = $distribution_query->execute()->fetchAll(\PDO::FETCH_ASSOC);
        // Extract the distribution identifier value from the associative array
        // We know this will only be one level
        $distribution_identifier = $distribution_result[0]['identifier'];
        if ($distribution_identifier) {
          // Echo for info's sake
          echo 'Associated Distribution Identifier: ' . $distribution_identifier  . PHP_EOL;
          // Now we have the distribution identifier so lets get the dataset ID
          // Associated with it from the node__field_json_metadata table 
          $dataset_query = \Drupal::database()->select('node__field_json_metadata', 'nfm');
          // Add JSON_EXTRACT to get the identifier
          $dataset_query->addExpression("JSON_UNQUOTE(JSON_EXTRACT(nfm.field_json_metadata_value, '$.identifier'))", 'identifier');
          // Add condition to check if the distribution array contains the value
          $dataset_query->condition(
              'nfm.field_json_metadata_value',
              '%' . \Drupal::database()->escapeLike($distribution_identifier) . '%',
              'LIKE'
          );
          // Execute query
          $dataset_result = $dataset_query->execute()->fetchAll(\PDO::FETCH_ASSOC);
          $dataset_identifier = $dataset_result[1]['identifier'];
          if ($dataset_identifier) {
            echo 'Associated Dataset: ' . $dataset_identifier . PHP_EOL;
            $this->output()->writeln('Dataset UUID: ' . $dataset_identifier);
            return DrushCommands::EXIT_SUCCESS;
          }
        }
      }
      $this->output()->writeln('Can not map data table to dataset: ' . $data_table_name);
      return DrushCommands::EXIT_FAILURE;
    }
  }
}
