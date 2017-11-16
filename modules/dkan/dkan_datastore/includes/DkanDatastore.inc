<?php

module_load_include("php", "dkan_datastore", "src/DatastoreInterface");

use Dkan\Datastore\Helper\Node;
use Dkan\Datastore\DatastoreInterface;

/**
 * Class for DKAN Datastores.
 */
class DkanDatastore implements DatastoreInterface {

  private $node;

  private $feedsConfig = [];

  private $feedsSource;

  /**
   * Sets default items for datastore object.
   */
  public function __construct($uuid) {
    module_load_include("php", "dkan_datastore", "src/Helper/Node");
    $this->node = Node::getNodeFromUuid($uuid);
  }

  /**
   * A machine readable identifier for a datastore.
   */
  public function getId() {
    return $this->getImporterId();
  }

  /**
   * A label is a human readable identifier for the datastore.
   */
  public function getLabel() {
    return $this->getImporterName();
  }

  /**
   * {@inheritdoc}
   */
  public function getNode() {
    return $this->node;
  }

  /**
   * Progress of the import (0 - 1).
   */
  public function getImportProgress() {
    return $this->getFeedsSource()->progressImporting();
  }

  /**
   * Provides themed status message for import.
   */
  public function getStatusMessage() {
    module_load_include('inc', 'feeds', 'feeds.pages');
    return feeds_source_status($this->getFeedsSource());
  }

  /**
   * Starts import.
   */
  public function import() {
    $source = $this->getFeedsSource();
    $source->addConfig($this->feedsConfig['parser']);
    $source->addConfig($this->feedsConfig['processor']);
    $source->save();
    $source->startImport();
  }

  /**
   * {@inheritdoc}
   */
  public function drop() {
    module_load_include('module', 'data', 'data');
    module_load_include('module', 'dkan_datastore', 'dkan_datastore');

    foreach ($this->getTables() as $table_name) {
      $has_file = dkan_datastore_file_field($this->node);
      $node = entity_metadata_wrapper('node', $this->node);
      $status = ($has_file) ? DKAN_DATASTORE_FILE_EXISTS : DKAN_DATASTORE_EMPTY;
      $node->field_datastore_status->set($status);
      $node->save();
      $this->dropTable($table_name);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function delete() {
    $source = $this->getFeedsSource();
    $source->startClear();
    $source->ensureSchedule();
  }

  /**
   * {@inheritdoc}
   */
  public function exists() {
    $tables_to_drop = $this->getTables();

    if (!empty($tables_to_drop)) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getConfigForm(&$form_state) {
    $form['settings'] = array(
      '#type' => 'fieldset',
      '#title' => t('Settings'),
    );
    $form['settings'] = $this->getFeedsSource()->configForm($form_state);
    $form['#count'] = $this->getFeedsSource()->itemCount();

    $form['description']['#weight'] = '-10';
    $form['source_status']['#weight'] = '-9';

    if (isset($form['settings']['FeedsCSVParser']['help'])) {
      unset($form['settings']['FeedsCSVParser']['help']);
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function configFormSubmitHandler(&$form_state) {
    $source = $this->getFeedsSource();

    $parser = $source->importer->config['parser']['plugin_key'];
    $processor = $source->importer->config['processor']['plugin_key'];

    $this->feedsConfig['parser'] = array($parser => $form_state['values'][$parser]);
    $this->feedsConfig['processor'] = array($processor => $form_state['values'][$processor]);
  }

  /**
   * Get tables.
   */
  private function getTables() {
    module_load_include('module', 'data', 'data');
    module_load_include('inc', 'feeds', 'feeds.pages');

    $tables = [];
    $importer_ids = feeds_get_importer_ids($this->node->type);
    foreach ($importer_ids as $importer_id) {
      $source = feeds_source($importer_id, $this->node->nid);
      $table_name = feeds_flatstore_processor_table_name($source->id, $source->feed_nid);
      $table = data_get_table($table_name) ? data_get_table($table_name) : db_table_exists($table_name);
      if ($table) {
        array_push($tables, $table_name);
      }
    }

    return $tables;
  }

  /**
   * Drop a datastore table.
   */
  private function dropTable($table_name) {
    $table = data_get_table($table_name);
    if ($table) {
      $table->drop();
    }
    elseif (db_table_exists($table_name)) {
      db_drop_table($table_name);
    }
  }

  /**
   * Get the feeds source.
   *
   * @return FeedsSource
   *   The feed source.
   */
  protected function getFeedsSource() {
    if (!$this->feedsSource) {
      $this->feedsSource = feeds_source($this->getImporterId(), $this->node->nid);
    }
    return $this->feedsSource;
  }

  /**
   * Get importer name.
   */
  private function getImporterName() {
    /* @var $importer FeedsImporter */
    $importer = $this->getFeedsImporter();
    return $importer->getConfig()['name'];
  }

  /**
   * Get importer id.
   */
  private function getImporterId() {
    $id = db_query("SELECT id FROM {feeds_source} WHERE feed_nid = :nid", array(':nid' => $this->node->nid))->fetchField();

    if (!$id) {
      $id = feeds_get_importer_id($this->getNode()->type);
    }

    if (!$id) {
      throw new \Exception("No Feeds Importer setup.");
    }
    return $id;
  }

  /**
   * Revrieves importer from $node if it exists.
   */
  private function getFeedsImporter() {
    return feeds_importer($this->getImporterId(), $this->node->nid);
  }

}
