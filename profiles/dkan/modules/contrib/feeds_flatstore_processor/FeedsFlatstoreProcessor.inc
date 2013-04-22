<?php

/**
 * @file
 * Definition of FeedsDataProcessor.
 */

/**
 * Creates simple table records from feed items. Uses Data module.
 */
class FeedsFlatstoreProcessor extends FeedsProcessor {

  // We aren't creating or updating entities, just table records.
  // So these are empty.
  /**
   * Modifies entity type.
   */
  public function entityType() {}

  /**
   * Modifies new entity.
   */
  protected function newEntity(FeedsSource $source) {}

  /**
   * Modifies entity save.
   */
  protected function entitySave($entity) {}

  /**
   * Modifies entity deletion.
   */
  protected function entityDeleteMultiple($entity_ids) {}

  /**
   * Implementation of FeedsProcessor::process().
   */
  public function process(FeedsSource $source, FeedsParserResult $parser_result) {
    // Count number of created and updated nodes.
    $inserted  = $updated = 0;
    $expiry_time = $this->expiryTime();
    $this->feed_nid = $source->feed_nid;

    $table = $this->table($source, $parser_result);

    while ($data = $parser_result->shiftItem()) {
      foreach ($data as $label => $value) {
        $cleaned_data[data_safe_name($label)] = $value;
      }
      $cleaned_data['feed_entity_id'] = $this->feed_nid;
      if (!isset($data['timestamp'])) {
        $cleaned_data['timestamp'] = FEEDS_REQUEST_TIME;
      }

      // We only save, never update, since there is only a flat table.
      $this->handler($source)->save($cleaned_data);
    }

  }

  /**
   * Count items imported by this source.
   */
  public function itemCount(FeedsSource $source) {
    $table_name = $this->tableName($source);
    if (db_table_exists($table_name)) {
      return db_query("SELECT count(*) FROM {" . $table_name . "}")->fetchField();
    }
    else {
      return NULL;
    }
  }

  /**
   * Implementation of FeedsProcessor::clear().
   *
   * Delete all data records for feed_entity_id in this table.
   */
  public function clear(FeedsSource $source) {
    $table_name = $this->tableName($source);
    $count = $this->itemCount($source);
    if ($count) {
      db_truncate($table_name)->execute();
      drupal_set_message(t('@count items have been deleted.', array('@count' => $count)));
    }
    else {
      drupal_set_message('There were no items to delete.');
    }
  }

  public function schedule() {
    $source = feeds_source($this->id, $this->feed_nid);
    $job = array(
       'type' => $this->id,
       'period' => 0,
       'periodic' => TRUE,
     );
     if (FEEDS_EXPIRE_NEVER != $this->processor->expiryTime()) {
       $job['period'] = 3600;
       JobScheduler::get('feeds_importer_expire')->set($job);
     }
     else {
       JobScheduler::get('feeds_importer_expire')->remove($job);
     }
  }

  /**
   * Override parent::configDefaults().
   */
  public function configDefaults() {
    return array(
      'update_existing' => FEEDS_SKIP_EXISTING,
       // Don't expire items by default.
      'expire' => FEEDS_EXPIRE_NEVER,
      'mappings' => array(),
      'delete_with_source' => FALSE,
    );
  }

  /**
   * Override parent::configForm().
   */
  public function configForm(&$form_state) {
    $form['no_config'] = array(
      '#type' => 'item',
      '#title' => t('No Configuration Options'),
      '#markup' => t('This processor does not have any configuration options.'),
    );
    return $form;
  }

  /**
   * Reschedule if expiry time changes.
   */
  public function configFormSubmit(&$values) {
    feeds_reschedule($this->id);
  }

  /**
   * Return the data table name for this feed.
   */
  protected function tableName(FeedsSource $source) {
    return feeds_flatstore_processor_table_name($source->id, $source->feed_nid);
  }

  /**
   * Return the data table for this feed.
   *
   * @throws Exception $e
   *   Throws this exception if a table cannot be found and cannot be created.
   */
  protected function table(FeedsSource $source, $parser_result) {
    if ($table = feeds_flatstore_processor_table($source, $parser_result)) {
      return $table;
    }
    else {
      throw new Exception(t('Could not create data table.'));
    }
  }

  /**
   * Return a data handler for this table.
   *
   * Avoids a call to table() to not unnecessarily instantiate DataTable.
   */
  protected function handler(FeedsSource $source) {
    return DataHandler::instance($this->tableName($source), 'id');
  }


  /**
   * Finds appropriate schema data types for each row.
   */
  public function dataSchema($parsed_results) {
    return feeds_flatstore_processor_find_columns($parsed_results);
  }

  /**
   * Creates full schema from baseSchema and an array.
   *
   * @param array $data
   *   Array with keys for each desired row.
   */
  public function fullSchema($data) {
    $base = $this->baseSchema();
    $data_schema = data_build_schema($data);
    $base['fields'] = array_merge($data_schema['fields'], $base['fields']);
    return $base;
  }

  /**
   * Every Feeds data table must have these elements.
   */
  protected function baseSchema() {
    return array(
      'fields' => array(
        'id' => array(
          'type' => 'serial',
          'size' => 'normal',
          'unsigned' => TRUE,
          'not null' => TRUE,
        ),
        'timestamp' => array(
          'description' => 'The Unix timestamp for the data.',
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => FALSE,
        ),
      ),
      'indexes' => array(
        'id' => array('id'),
        'timestamp' => array('timestamp'),
      ),
      'primary key' => array(
        '0' => 'id',
      ),
    );
  }
}
