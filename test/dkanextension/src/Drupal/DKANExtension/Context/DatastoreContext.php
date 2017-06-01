<?php

namespace Drupal\DKANExtension\Context;
use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;

/**
 * Defines application features from the specific context.
 */
class DatastoreContext extends RawDKANContext {

  /**
   * @BeforeScenario
   */
  public function gatherContexts(BeforeScenarioScope $scope){
    // Change /data.json path to /json during tests.
    $data_json = open_data_schema_map_api_load('data_json_1_1');
    $data_json->endpoint = 'json';
    drupal_write_record('open_data_schema_map', $data_json, 'id');
    drupal_static_reset('open_data_schema_map_api_load_all');
    menu_rebuild();

    parent::gatherContexts($scope);
    $environment = $scope->getEnvironment();
    $this->pageContext = $environment->getContext('Drupal\DKANExtension\Context\PageContext');
  }

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
    // Restore /data.json path
    $data_json = open_data_schema_map_api_load('data_json_1_1');
    $data_json->endpoint = 'data.json';
    drupal_write_record('open_data_schema_map', $data_json, 'id');
    drupal_static_reset('open_data_schema_map_api_load_all');
    menu_rebuild();

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
   * @Then I :outcome be able to manage the :resource_title datastore
   */
  public function iBeAbleToManageTheDatastore($outcome, $resource_title)
  {
    // Throw an exception if the outcome is not a valid value.
    if (!in_array($outcome, array('should', 'should not'))) {
      throw new \Exception("$outcome value is not valid.");
    }

    // Get node ID associated with the specified title.
    $node_id = $this->getNidByTitle($resource_title);
    if (empty($node_id)) {
      throw new \Exception("Resource with the title '$resource_title' doesn't exist.");
    }

    // Try to visit every datastore page associated with the specified resource.
    $session = $this->getSession();
    $base_path = 'node/' . $node_id;
    $datastore_paths = array(
            '/datastore',
            '/datastore/delete-items',
            '/datastore/unlock',
            '/datastore/drop',
            '/datastore/clear'
    );

    foreach ($datastore_paths as $datastore_path) {
      $full_path =  $base_path . $datastore_path;
      $session = $this->visit($full_path, $session);
      $status_code = $this->getStatusCode();
      $on_login = $this->pageContext->containsBasePath($session, '/user/login');

      if ($status_code != 403 && $status_code != 200) {
        throw new \Exception("A $status_code error was thrown when visiting the URL '$full_path'.");
      }
      if ($outcome === 'should' && $status_code != 200) {
        throw new \Exception("The user is not able to access the '$full_path' URL.");
      }

      if ($outcome === 'should not' && $status_code == 200 && !$on_login) {
        throw new \Exception("The user is able to access the '$full_path' URL.");
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
