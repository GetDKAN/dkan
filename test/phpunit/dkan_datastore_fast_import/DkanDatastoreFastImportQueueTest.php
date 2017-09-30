<?php

/**
 * @file
 * Test that quequed imports run as expected.
 */

/**
 * DkanDatastoreFastImport class.
 */
class DkanDatastoreFastImportQueueTest extends \PHPUnit_Framework_TestCase {

  /**
   * {@inheritdoc}
   */
  public static function setUpBeforeClass() {
    if (!module_exists('dkan_datastore_fast_import')) {
      module_enable(array('dkan_datastore_fast_import'));
    }
  }

  /**
   * Test that new rows aren't just tacked to end of data store.
   */
  public function testMultipleQueueImportSingleDatastore() {
    // This resource uuid is from dkan fixtures.
    $uuid = '960687d8-582c-4f29-b1e4-113781e58e3b';
    $nid = self::getNodeFromUuid($uuid);
    $importerId = 'dkan_file';
    $node = node_load($nid);
    $node = entity_metadata_wrapper('node', $node);
    $source = feeds_source($importerId, $nid);
    $table = feeds_flatstore_processor_table($source, array());
    $config = array(
      'delimiter' => ',',
      'no_headers' => 0,
      'encoding' => 'UTF-8',
    );

    variable_set('dkan_datastore_load_data_type', 'load_data_infile');
    variable_set('quote_delimiters', '"');
    variable_set('lines_terminated_by', '\n');
    variable_set('fields_escaped_by', '');
    variable_set('dkan_datastore_fast_import_load_empty_cells_as_null', 0);

    $item = array(
      'source' => $source,
      'node' => $node,
      'table' => $table,
      'config' => $config,
    );

    // Queue and run import three times.
    DrupalQueue::get(dkan_datastore_fast_import_queue_name())->createItem($item);
    drupal_cron_run();

    DrupalQueue::get(dkan_datastore_fast_import_queue_name())->createItem($item);
    drupal_cron_run();

    DrupalQueue::get(dkan_datastore_fast_import_queue_name())->createItem($item);
    drupal_cron_run();

    $datastore = Datastore::instance("DkanDatastoreFastImport", $uuid);
    $result = db_select($datastore->tableName, 'ds')->fields('ds')->execute();

    $expected = 2969;
    $actual = $result->rowCount();
    $this->assertEquals($actual, $expected);

    $datastore->dropDataStore();
  }

  /**
   * Get the node id from a known uuid.
   */
  public static function getNodeFromUuid($uuid) {
    $ids = entity_get_id_by_uuid('node', array($uuid));
    foreach ($ids as $uid => $id) {
      return $id;
    }
  }

}
