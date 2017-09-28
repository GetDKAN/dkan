<?php

/**
 * @file
 * DKAN Datatore implementation of Datastore class.
 */

/**
 * Class for DKAN Datastores.
 */
class DkanDatastoreFastImport extends DkanDatastore implements DatastoreFormInterface {

  public $endpoint = 'api/action/datastore/search.json';
  const QUEUE_FILESIZE_THRESHOLD_DEFAULT = '30MB';
  const FAST_IMPORT_THRESHOLD_DEFAULT = '30MB';

  /**
   * Sets default items for datastore object.
   */
  protected function __construct($uuid, $config = array(
    'delimiter' => ',',
    'no_headers' => 0,
    'encoding' => 'UTF-8',
  )) {
    parent::__construct($uuid);
    $this->config = $config;
    $load_data_type = variable_get('dkan_datastore_load_data_type', 'load_data_local_infile');
    $this->table = clone feeds_flatstore_processor_table($this->source, array());
    $this->load_data_statement = ($load_data_type === 'load_data_local_infile') ? 'LOAD DATA LOCAL' : 'LOAD DATA';
  }

  /**
   * Get header names as sql variables.
   */
  private function getFieldsAsVars($headers) {
    $vars = array();
    foreach ($headers as $header) {
      $vars[] = '@v' . $header;
    }
    return $vars;
  }

  /**
   * Takes values from form submit, saves updated config and starts import.
   */
  public function manageFormSubmit(&$form_state) {
    $importer_id = $form_state['values']['importer_id'];
    $source = feeds_source($importer_id, $this->node->nid);
    $parser = $source->importer->config['parser']['plugin_key'];
    $processor = $source->importer->config['processor']['plugin_key'];
    $this->addConfig(array($parser => $form_state['values'][$parser]));
    $this->addConfig(array($processor => $form_state['values'][$processor]));
    $this->save();

    // Bypass feeds to improve the import performance.
    $node = entity_metadata_wrapper('node', $form_state['build_info']['args'][0]);
    $file = $node->field_upload->value();
    $file_remote = $node->field_link_remote_file->value();
    $filesize = filesize(drupal_realpath($file->uri));
    $use_fast_import = $form_state['values']['use_fast_import'];
    $queue_filesize_threshold = parse_size(variable_get('queue_filesize_threshold', self::QUEUE_FILESIZE_THRESHOLD_DEFAULT));
    variable_set('quote_delimiters', $form_state['values']['quote_delimiters']);
    variable_set('lines_terminated_by', $form_state['values']['lines_terminated_by']);
    variable_set('fields_escaped_by', $form_state['values']['fields_escaped_by']);
    variable_set('dkan_datastore_fast_import_load_empty_cells_as_null', $form_state['values']['dkan_datastore_fast_import_load_empty_cells_as_null']);

    // If a remote file is provided we fallback to feeds importer
    // else we perform a fast import.
    if (!empty($file_remote) || !$use_fast_import) {
      $this->dropDataStore();
      $this->import();
    }
    else {
      // Avoid the queueing the resource if it's small.
      if ($filesize > $queue_filesize_threshold) {
        $item = array('config' => $form_state['values'][$parser]);
        DrupalQueue::get(dkan_datastore_fast_import_queue_name())->createItem($item);
        drupal_set_message(t('File was succesfully enqueued to be imported and will be available in the datastore in a few minutes'), 'status');
      }
      else {
        try {
          $this->config = $form_state['values'][$parser];
          $this->fastImport();
          drupal_set_message(t('File was succesfully imported into the datastore'), 'status');
        }
        catch (Exception $e) {
          drupal_set_message(t('An error occurred trying to import this file: @error', array('@error' => $e->getMessage())), 'error');
        }
      }
    }
  }

  /**
   * Import using fast import SQL.
   */
  public function fastImport() {
    $table = $this->table;
    $node = entity_metadata_wrapper('node', $this->node);
    $file = $node->field_upload->value();
    $file_path = drupal_realpath($file->uri);
    $feeds_entity_id = $this->source->feed_nid;
    $headers = array_keys($table->meta['fields']);
    $fields = implode(',', $headers);
    $delim = $this->config['delimiter'];
    $has_headers = ($this->config['no_headers']) ? '' : 'IGNORE 1 LINES';
    $quote_delimiters = variable_get('quote_delimiters', '"');
    $lines_terminated_by = variable_get('lines_terminated_by', '\n');
    $fields_escaped_by = variable_get('fields_escaped_by', '');
    $empty_as_null = variable_get('dkan_datastore_fast_import_load_empty_cells_as_null', 0);
    $set_null_values = '';
    $params = array();

    // If importing empty values as null, create a local var for each column.
    // See https://stackoverflow.com/questions/2675323/mysql-load-null-values-from-csv-data
    if ($empty_as_null) {
      $vars = $this->getFieldsAsVars($headers);
      $fields = implode(',', $vars);
      $headers_to_vars = array_combine($headers, $vars);
      foreach ($headers_to_vars as $header => $var) {
        $set_null_values = $set_null_values . ", $header = nullif($var,'')";
      }
    }

    $sql = "{$this->load_data_statement} INFILE :file_path IGNORE
      INTO TABLE {$table->name}
      FIELDS TERMINATED BY :delim
      ENCLOSED BY :quote_delimiters";
    $params[':file_path'] = $file_path;
    $params[':delim'] = $delim;
    $params[':quote_delimiters'] = $quote_delimiters;

    if ($fields_escaped_by) {
      $sql = $sql . "  ESCAPED BY :fields_escaped_by";
      $params[':fields_escaped_by'] = $fields_escaped_by;
    }
    $sql = $sql . " LINES TERMINATED BY '$lines_terminated_by' $has_headers ($fields)
      SET timestamp=UNIX_TIMESTAMP(), feeds_entity_id=$feeds_entity_id $set_null_values;";

    try {
      $result = db_query($sql, $params);
      if ($result) {
        if ($result->rowCount() == 0) {
          drupal_set_message(t('There were no items imported. It may be due to a misconfiguration related to characters set as quote delimiters, lines terminators or escape characters.'), 'warning', FALSE);
        }
      }
      $node->field_datastore_status->set(DKAN_DATASTORE_EXISTS);
      $node->save();
      return array('total_imported_items' => $result->rowCount());
    }
    catch (Exception $e) {
      drupal_set_message(t('There was an error trying to import this file: @error', array('@error' => $e->getMessage())), 'error', FALSE);
      return array('error', $e);
    }
  }

}
