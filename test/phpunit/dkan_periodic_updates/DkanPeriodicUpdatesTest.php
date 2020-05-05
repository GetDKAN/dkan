<?php

use Dkan\Datastore\Manager\SimpleImport\SimpleImport;
use Dkan\Datastore\Resource;

module_load_include('module', 'dkan_periodic_updates');

/**
 * Class DkanPeriodicUpdatesTest.
 */
class DkanPeriodicUpdatesTest extends \PHPUnit_Framework_TestCase {

  private $resources;
  private $manifest;

  protected function setUp() {
    // Daily update.
    $file_url = "https://s3.amazonaws.com/dkan-default-content-files/district_centerpoints_small.csv";
    $file = file_save((object)[
      'filename' => drupal_basename($file_url),
      'uri' => $file_url,
      'status' => FILE_STATUS_PERMANENT,
      'filemime' => file_get_mimetype($file_url),
    ]);
    $node = (object)[];
    $node->title = "Resource Test Object - Daily Update";
    $node->type = "resource";
    $node->field_upload[LANGUAGE_NONE][0]['fid'] = $file->fid;
    $node->status = 1;
    $node->uuid = 'c65c08d9-43c8-45a4-a49d-29e714ce2ebb';
    node_save($node);
    $this->resources[$node->nid] = node_load($node->nid);

    // Weekly update.
    $node = (object)[];
    $node->title = "Resource Test Object - Weekly Update";
    $node->type = "resource";
    $node->field_link_remote_file[LANGUAGE_NONE][0]['fid'] = $file->fid;
    $node->field_link_remote_file[LANGUAGE_NONE][0]['display'] = 1;
    $node->status = 1;
    $node->uuid = 'd7ccef48-5c8c-432c-94c8-17cee9c4ed37';
    node_save($node);
    $this->resources[$node->nid] = node_load($node->nid);

    // Monthly update.
    $node = (object)[];
    $node->title = "Resource Test Object - Monthly Update";
    $node->type = "resource";
    $node->field_link_remote_file[LANGUAGE_NONE][0]['fid'] = $file->fid;
    $node->field_link_remote_file[LANGUAGE_NONE][0]['display'] = 1;
    $node->status = 1;
    $node->uuid = 'fe4b712b-c570-4e0b-aeeb-67d9789a196e';
    node_save($node);
    $this->resources[$node->nid] = node_load($node->nid);

    // Set manifest.
    $this->setManifest();
  }

  protected function setManifest() {
    $file = file_get_contents(__DIR__ . '/data/test_manifest.csv');
    $file = file_save_data($file, 'public://test_manifest.csv');
    $this->manifest = $file->fid;
  }

  public function testAmountFirstTimeUpdates() {
    // All elements from manifest should be updated.
    $to_update = dkan_periodic_updates_get_items_to_update($this->manifest);
    $this->assertEquals(4, count($to_update));
  }

  public function testAmountDailyUpdates() {
    $date = new DateTime("now");
    // Set last_update date to three hours ago for existing resources.
    // One element should be updated.
    variable_set('dkan_periodic_updates_c65c08d9-43c8-45a4-a49d-29e714ce2ebb', $date->modify('-3 hour'));
    variable_set('dkan_periodic_updates_d7ccef48-5c8c-432c-94c8-17cee9c4ed37', $date->modify('-3 hour'));
    variable_set('dkan_periodic_updates_fe4b712b-c570-4e0b-aeeb-67d9789a196e', $date->modify('-3 hour'));
    $to_update = dkan_periodic_updates_get_items_to_update($this->manifest);
    $this->assertEquals(1, count($to_update));

    // Set last_update date to 1+ day ago to Daily Update Test Object.
    // Just two element should be updated.
    variable_set('dkan_periodic_updates_c65c08d9-43c8-45a4-a49d-29e714ce2ebb', $date->modify('-2 day'));
    $to_update = dkan_periodic_updates_get_items_to_update($this->manifest);
    $this->assertEquals(2, count($to_update));
  }

  public function testAmountWeeklyUpdates() {
    $date = new DateTime("now");
    // Set last_update date to 7+ day ago to Weekly Update Test Object.
    // Just two elements should be updated.
    variable_set('dkan_periodic_updates_c65c08d9-43c8-45a4-a49d-29e714ce2ebb', $date->modify('-3 hour'));
    $date = new DateTime("now");
    variable_set('dkan_periodic_updates_d7ccef48-5c8c-432c-94c8-17cee9c4ed37', $date->modify('-8 day'));
    $date = new DateTime("now");
    variable_set('dkan_periodic_updates_fe4b712b-c570-4e0b-aeeb-67d9789a196e', $date->modify('-9 day'));
    $to_update = dkan_periodic_updates_get_items_to_update($this->manifest);
    $this->assertEquals(2, count($to_update));
  }

  public function testAmountMonthlyUpdates() {
    $date = new DateTime("now");
    // Set last_update date to 29+ day ago to Monthly Update Test Object.
    // Just two element should be updated.
    variable_set('dkan_periodic_updates_c65c08d9-43c8-45a4-a49d-29e714ce2ebb', $date->modify('-3 hour'));
    $date = new DateTime("now");
    variable_set('dkan_periodic_updates_d7ccef48-5c8c-432c-94c8-17cee9c4ed37', $date->modify('-4 day'));
    $date = new DateTime("now");
    variable_set('dkan_periodic_updates_fe4b712b-c570-4e0b-aeeb-67d9789a196e', $date->modify('-30 day'));
    $to_update = dkan_periodic_updates_get_items_to_update($this->manifest);
    $this->assertEquals(2, count($to_update));
  }

  public function testImportDatastore() {
    $to_update = dkan_periodic_updates_get_items_to_update($this->manifest);
    $this->assertEquals(4, count($to_update));
    $this->assertTrue($to_update['c65c08d9-43c8-45a4-a49d-29e714ce2ebb']['datastore']);
    $this->assertFalse($to_update['d7ccef48-5c8c-432c-94c8-17cee9c4ed37']['datastore']);
    $this->assertFalse($to_update['fe4b712b-c570-4e0b-aeeb-67d9789a196e']['datastore']);
    $this->assertFalse($to_update['non-existing-uuid']['datastore']);
  }

  public function testUpdatesDisabled() {
    // Updates disabled.
    variable_set('dkan_periodic_updates_status', FALSE);
    $result = dkan_periodic_updates_state();
    $message_disabled = '<p class="alert alert-warning">Periodic updates are disabled.</p>';
    $this->assertEquals($message_disabled, $result['state']['#markup']);
  }

  public function testUpdatesWithoutManifest() {
    // Updates enabled, no manifest.
    variable_set('dkan_periodic_updates_status', TRUE);
    variable_del('dkan_periodic_updates_manifest');
    $result = dkan_periodic_updates_state();
    $message_no_manifest = '<p class="alert alert-warning">No manifest was found.</p>';
    $this->assertEquals($message_no_manifest, $result['state']['#markup']);
  }

  public function testUpdates() {
    variable_set('dkan_periodic_updates_status', TRUE);
    variable_set('dkan_periodic_updates_manifest', $this->manifest);
    $to_update = dkan_periodic_updates_get_items_to_update($this->manifest);
    $this->assertEquals(4, count($to_update));
    dkan_periodic_updates_execute_update($to_update);
    $result = dkan_periodic_updates_state();

    // Assert last update is set for daily, weekly and monthly updated resources.
    // This means the update was executed.
    $this->assertEquals("No errors found.", $result['state']['#rows'][0][3]);
    $this->assertNotEquals(" - ", $result['state']['#rows'][0][4]);
    $this->assertEquals("No errors found.", $result['state']['#rows'][1][3]);
    $this->assertNotEquals(" - ", $result['state']['#rows'][1][4]);
    $this->assertEquals("No errors found.", $result['state']['#rows'][2][3]);
    $this->assertNotEquals(" - ", $result['state']['#rows'][2][4]);

    // Assert empty frequency is shown as daily.
    $this->assertEquals("daily", $result['state']['#rows'][3][2]);
    // Assert status about no node found for UUID specified.
    $this->assertEquals("No node found for the UUID specified.", $result['state']['#rows'][3][3]);
    // Assert last update is not set for non existing node with UUID specified.
    $this->assertEquals(" - ", $result['state']['#rows'][3][4]);

    // Try to import again and confirm already imported items don't need to be updated.
    $to_update = dkan_periodic_updates_get_items_to_update($this->manifest);
    $this->assertEquals(1, count($to_update));
  }

  protected function tearDown() {
    foreach ($this->resources as $resource) {
      variable_del('dkan_periodic_updates_' . $resource->uuid);
      variable_del('dkan_periodic_updates_message_' . $resource->uuid);
    }
    node_delete_multiple(array_keys($this->resources));
  }
}
