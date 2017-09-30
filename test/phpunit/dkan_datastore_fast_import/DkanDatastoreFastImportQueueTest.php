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
   * Test that new rows aren't just tacked to end of data store.
   */
  public function testMultipleQueueImportSingleDatastore() {
    // This resource uuid is from dkan fixtures.
    $uuid = '960687d8-582c-4f29-b1e4-113781e58e3b';
    $config = array(
      'delimiter' => ',',
      'no_headers' => 0,
      'encoding' => 'UTF-8',
    );
    $item = array('config' => $config);

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

    $datastore->dropDataStore();
  }

}
