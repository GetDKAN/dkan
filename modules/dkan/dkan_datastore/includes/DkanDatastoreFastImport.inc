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
  protected function __construct($uuid) {
    parent::__construct($uuid);
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
    $table = feeds_flatstore_processor_table($source, array());
    $node = entity_metadata_wrapper('node', $form_state['build_info']['args'][0]);
    $file = $node->field_upload->value();
    $file_remote = $node->field_link_remote_file->value();
    $filesize = filesize(drupal_realpath($file->uri));
    $use_fast_import = $form_state['values']['use_fast_import'];
    $queue_filesize_threshold = parse_size(variable_get('queue_filesize_threshold', self::QUEUE_FILESIZE_THRESHOLD_DEFAULT));
    variable_set('quote_delimiters', $form_state['values']['quote_delimiters']);
    variable_set('lines_terminated_by', $form_state['values']['lines_terminated_by']);
    variable_set('fields_escaped_by', $form_state['values']['fields_escaped_by']);

    // If a remote file is provided we fallback to feeds importer
    // else we perform a fast import.
    if (!empty($file_remote) || !$use_fast_import) {
      $this->import();
    }
    else {
      // Avoid the queueing the resource if it's small.
      if ($filesize > $queue_filesize_threshold) {
        $item = array(
          'source' => $source,
          'node' => $node,
          'table' => $table,
          'config' => $form_state['values'][$parser],
        );
        DrupalQueue::get(dkan_datastore_fast_import_queue_name())->createItem($item);
        drupal_set_message(t('File was succesfully enqueued to be imported and will be available in the datastore in a few minutes'), 'status');
      }
      else {
        try {
          dkan_datastore_fast_import_import($source, $node, $table, $form_state['values'][$parser]);
          drupal_set_message(t('File was succesfully imported into the datastore'), 'status');
        }
        catch (Exception $e) {
          drupal_set_message(t('An error occurred trying to import this file: @error', array('@error' => $e->getMessage())), 'error');
        }
      }
    }
  }

}
