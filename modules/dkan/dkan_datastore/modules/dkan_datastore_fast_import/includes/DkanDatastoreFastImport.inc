<?php

/**
 * Class DkanDatastoreFastImport.
 */
class DkanDatastoreFastImport extends DkanDatastore {

  const FAST_IMPORT_THRESHOLD_DEFAULT = '30MB';
  const QUEUE_FILESIZE_THRESHOLD_DEFAULT = '30MB';

  private $enabled = FALSE;

  /**
   * {@inheritdoc}
   */
  public function getConfigForm(&$form_state) {
    $form = parent::getConfigForm($form_state);

    variable_set('quote_delimiters', '"');
    variable_set('lines_terminated_by', '\n');
    variable_set('fields_escaped_by', '');
    variable_set('dkan_datastore_fast_import_load_empty_cells_as_null', 0);

    $use_fast_import = FALSE;

    // Use global fast import setting to inform the state of
    // the configuration form.
    $selection = (int) variable_get('dkan_datastore_fast_import_selection', DKAN_DATASTORE_FAST_IMPORT_DKAN_DATASTORE_BATCH_IMPORT);
    if ($selection != DKAN_DATASTORE_FAST_IMPORT_DKAN_DATASTORE_BATCH_IMPORT) {
      $use_fast_import = TRUE;
    }

    $form['#attached']['js'][] = array(
      'data' => drupal_get_path('module', 'dkan_datastore_fast_import') . '/dkan_datastore_fast_import.js',
      'type' => 'file',
    );

    $form['use_fast_import'] = array(
      '#type' => 'checkbox',
      '#title' => t('Use Fast Import'),
      '#default_value' => $use_fast_import,
      '#states' => array(
        'disabled' => array(
          ':input[name="FeedsFlatstoreProcessor[geolocate]"]' => array('checked' => TRUE),
        ),
      ),
    );

    $form['quote_delimiters'] = array(
      '#type' => 'select',
      '#title' => t('Quote delimiters'),
      '#options' => array(
        '\'' => '\'',
        '"' => '"',
        '~' => '~',
      ),
      '#default_value' => variable_get('quote_delimiters', '"'),
      '#description' => t('The character that will be used to enclose fields in this CSV file.'),
      '#states' => array(
        'invisible' => array(
          ':input[name="use_fast_import"]' => array('checked' => FALSE),
        ),
      ),
    );

    $form['lines_terminated_by'] = array(
      '#type' => 'select',
      '#title' => t('Lines terminated by:'),
      '#options' => array(
        '\n' => '\n (Unix)',
        '\r\n' => '\r\n (Windows)',
        '\r' => '\r (Legacy Mac)',
      ),
      '#default_value' => variable_get('lines_terminated_by', '\n'),
      '#states' => array(
        'invisible' => array(
          ':input[name="use_fast_import"]' => array('checked' => FALSE),
        ),
      ),
    );

    $form['fields_escaped_by'] = array(
      '#type' => 'textfield',
      '#title' => t('Fields escaped by:'),
      '#default_value' => variable_get('fields_escaped_by', ''),
      '#description' => t('The character used to escape other characters on each fields. Leave empty if it isn\'t needed.'),
      '#states' => array(
        'invisible' => array(
          ':input[name="use_fast_import"]' => array('checked' => FALSE),
        ),
      ),
    );

    $form['dkan_datastore_fast_import_load_empty_cells_as_null'] = array(
      '#type' => 'checkbox',
      '#title' => t('Read empty cells as NULL (if unchecked, empty cells will be read as zeros or empty strings).'),
      '#default_value' => variable_get('dkan_datastore_fast_import_load_empty_cells_as_null'),
      '#states' => array(
        'invisible' => array(
          ':input[name="use_fast_import"]' => array('checked' => FALSE),
        ),
      ),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function configFormSubmitHandler(&$form_state) {
    if ($form_state['values']['use_fast_import']) {
      $this->enabled = TRUE;
    }
    variable_set('quote_delimiters', $form_state['values']['quote_delimiters']);
    variable_set('lines_terminated_by', $form_state['values']['lines_terminated_by']);
    variable_set('fields_escaped_by', $form_state['values']['fields_escaped_by']);
    variable_set('dkan_datastore_fast_import_load_empty_cells_as_null', $form_state['values']['dkan_datastore_fast_import_load_empty_cells_as_null']);
  }

  /**
   * {@inheritdoc}
   */
  public function import() {
    $source = $this->getFeedsSource();

    $table = feeds_flatstore_processor_table($source, array());

    $node = entity_metadata_wrapper('node', $this->getNode());
    $file = $node->field_upload->value();
    $filesize = filesize(drupal_realpath($file->uri));

    $queue_filesize_threshold = parse_size(variable_get('queue_filesize_threshold', self::FAST_IMPORT_THRESHOLD_DEFAULT));

    if (!$this->enabled) {
      watchdog('fast', "SLOW IMPORT!!!");
      parent::import();
    }
    else {
      watchdog('fast', "FAST IMPORT!!!");
      if ($filesize >= $queue_filesize_threshold) {
        watchdog('fast', "FAST QUEUED IMPORT!!!");
        $this->queuedImport($source, $node, $table);
        drupal_set_message(t('File was succesfully enqueued to be imported and will be available in the datastore in a few minutes'), 'status');
      }
      else {
        watchdog('fast', "FAST NOW IMPORT!!!");
        try {
          $this->fastImport($source, $node, $table, $file, []);
          drupal_set_message(t('File was succesfully imported into the datastore'), 'status');
        }
        catch (Exception $e) {
          drupal_set_message(t('An error occurred trying to import this file: @error', array('@error' => $e->getMessage())), 'error');
        }
      }
    }
  }

  /**
   * Queued import.
   */
  private function queuedImport($source, $node, $table) {
    $item = array(
      'source' => $source,
      'node' => $node,
      'table' => $table,
      'config' => [],
    );
    DrupalQueue::get(dkan_datastore_fast_import_queue_name())->createItem($item);
  }

  /**
   * Fast import.
   */
  private function fastImport($source, $node, $table, $file, $config = []) {
    $load_data_type = variable_get('dkan_datastore_load_data_type', 'load_data_local_infile');

    $file_path = drupal_realpath($file->uri);
    $feeds_entity_id = $source->feed_nid;

    $headers = array_keys($table->meta['fields']);
    $fields = implode(',', $headers);

    $delim = isset($config['delimiter']) ? $config['delimiter'] : ",";
    $has_headers = (isset($config['no_headers']) && $config['no_headers']) ? '' : 'IGNORE 1 LINES';

    $quote_delimiters = variable_get('quote_delimiters', '"');
    $lines_terminated_by = variable_get('lines_terminated_by', '\n');
    $fields_escaped_by = variable_get('fields_escaped_by', '');
    $empty_as_null = variable_get('dkan_datastore_fast_import_load_empty_cells_as_null', 0);

    $set_null_values = '';

    $params = array();

    // If importing empty values as null, create a local var for each column.
    // See https://stackoverflow.com/questions/2675323/mysql-load-null-values-from-csv-data
    if ($empty_as_null) {
      $vars = dkan_datastore_fast_import_get_fields_as_vars($headers);
      $fields = implode(',', $vars);
      $headers_to_vars = array_combine($headers, $vars);
      foreach ($headers_to_vars as $header => $var) {
        $set_null_values = $set_null_values . ", $header = nullif($var,'')";
      }
    }

    $load_data_statement = ($load_data_type === 'load_data_local_infile') ? 'LOAD DATA LOCAL' : 'LOAD DATA';

    $sql = "{$load_data_statement} INFILE :file_path IGNORE INTO TABLE {$table->name} FIELDS TERMINATED BY :delim ENCLOSED BY :quote_delimiters ";
    $params[':file_path'] = $file_path;
    $params[':delim'] = $delim;
    $params[':quote_delimiters'] = $quote_delimiters;

    if ($fields_escaped_by) {
      $sql = $sql . "  ESCAPED BY :fields_escaped_by";
      $params[':fields_escaped_by'] = $fields_escaped_by;
    }

    $sql = $sql . " LINES TERMINATED BY '$lines_terminated_by' $has_headers ($fields) SET timestamp=UNIX_TIMESTAMP(), feeds_entity_id=$feeds_entity_id $set_null_values;";

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
