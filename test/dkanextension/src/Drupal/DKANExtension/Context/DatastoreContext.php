<?php

namespace Drupal\DKANExtension\Context;
use Behat\Behat\Hook\Scope\AfterScenarioScope;

/**
 * Defines application features from the specific context.
 */
class DatastoreContext extends RawDKANContext {

  /**
   * @Given I am on the resource :title
   */
  public function iAmOnResourcePage($title) {
    if (empty($title)) {
      throw new \Exception("Missing title argument");
    }
    $nid = $this->getNidByTitle($title);
    if (empty($nid)) {
      throw new \Exception("Resource with the title '$title' doesn't exist.");
    }
    $alias = drupal_get_path_alias('node/'. $nid);
    $this->visit($alias);
  }

  /**
   * @AfterScenario @datastore
   *
   * @param AfterScenarioScope $scope
   */
  public function dropDatastores($scope) {
    $result = db_query("SELECT n.nid FROM {node} n WHERE n.type = :type",array(":type" => "resource"));
    foreach ($result as $n) {
      if (!empty($n->nid)) {
        $node = node_load($n->nid);
        $importer_ids = feeds_get_importer_ids($node->type);
        foreach ($importer_ids as $importer_id) {
          $source = feeds_source($importer_id, $node->nid);
          $table_name = feeds_flatstore_processor_table_name($source->id, $source->feed_nid);
          $has_file = dkan_datastore_file_field($node);
          $wrapper = entity_metadata_wrapper('node', $node);
          $status = ($has_file) ? DKAN_DATASTORE_FILE_EXISTS : DKAN_DATASTORE_EMPTY;
          $wrapper->field_datastore_status->set($status);
          $wrapper->save();
          $this->dropTable($table_name);
        }
      }
    }
  }

  /**
   * @BeforeScenario @datastore
   *
   * @param BeforeScenarioScope $scope
   */
  public function resetDatastoreSettings($scope) {
    variable_set('dkan_datastore_fast_import_selection_threshold', '20MB');
    variable_set('queue_filesize_threshold', '20MB');
  }

  /**
   * Drop a datastore table
   */
  private function dropTable($table_name){
    $table = data_get_table($table_name);
    if($table) {
      $table->drop();
    } elseif (db_table_exists($table_name)) {
      db_drop_table($table_name);
    }
  }

  private function getNidByTitle($title) {
    $result = db_query("SELECT n.nid FROM {node} n WHERE n.title = :title AND n.type = :type", array(":title"=> $title, ":type"=> 'resource'));
    return $result->fetchField();
  }
}
