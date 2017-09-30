<?php

/**
 * @file
 * Test that quequed imports run as expected.
 */

/**
 * DkanDatastoreQueueTest class.
 */
class DkanDatastoreQueueTest extends \PHPUnit_Framework_TestCase {

  /**
   * {@inheritdoc}
   */
  public static function setUpBeforeClass() {
    if (!module_exists('dkan_datastore')) {
      module_enable(array('dkan_datastore'));
    }
  }

  /**
   * Test that new rows aren't just tacked to end of data store.
   */
  public function testMultipleQueueImportSingleDatastore() {
    // This resource uuid is from dkan fixtures.
    $uuid = '49daa44a-efa6-4145-92a9-baedc7202e1f';
    // Queue and run import three times.
    dkan_datastore_queue_import($uuid);
    drupal_cron_run();

    dkan_datastore_queue_import($uuid);
    drupal_cron_run();

    dkan_datastore_queue_import($uuid);
    drupal_cron_run();

    $datastore = dkan_datastore_go($uuid);

    $result = db_select($datastore->tableName, 'ds')->fields('ds')->execute();

    $expected = 10;
    $actual = $result->rowCount();
    $this->assertEquals($actual, $expected);
  }

}
