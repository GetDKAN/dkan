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
      $query = $connection->select('dkan_metastore_resource_mapper', 'dm')
        //->fields('dm', ['id', 'identifier', 'version', 'perspective']);
        ->fields('dm', ['identifier']);

        // Add the condition using a raw SQL expression.
        $query->where(
          'CONCAT(\'datastore_\', MD5(CONCAT(identifier, \'__\', version, \'__\', perspective))) = :data_table_name',
          [':data_table_name' => $data_table_name]
        );
        
        // Execute the query.
        $results = $query->execute();

        // Fetch the results as an array.
        $resource_id = $results->fetchAll(\PDO::FETCH_ASSOC);

        // Now we have our associated resource id so
        // Use it to find the dataset UUID

        //$dataset_query = 

        
        return $resource_id;
    }
    if ($data) {
      $this->output()->writeln('Dataset Info: ' . $data);
      return DrushCommands::EXIT_SUCCESS;
    }
    $this->output()->writeln('No resource for identifier: ' . $data_table_name);
    return DrushCommands::EXIT_FAILURE;
  }

}
