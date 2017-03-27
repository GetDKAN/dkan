<?php

/**
 * @file
 * Contains test phpunit class for HarvestMigration.
 */

include_once __DIR__ . '/includes/HarvestSourceDataJsonStub.php';

/**
 * Test class for the HarvestMigration class.
 *
 * @class DatajsonHarvestMigrationTest
 */
class DatajsonHarvestMigrationTest extends PHPUnit_Framework_TestCase {

  /**
   * {@inheritdoc}
   */
  public static function setUpBeforeClass() {
    $source = self::getOriginalTestSource();

    // Harvest cache the test source.
    dkan_harvest_cache_sources(array($source));
    // Harvest Migration of the test data.
    dkan_harvest_migrate_sources(array($source));

    // We need this module for the testResourceRedirect test.
    module_enable(array('dkan_harvest_test'));
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
  }

  /**
   * Test dataset count.
   */
  public function testDatasetCount() {
    $dataset_nids = $this->getTestDatasetNid(self::getOriginalTestSource());
    $this->assertEquals(1, count($dataset_nids));

    // Load the node emw.
    $dataset_node = entity_load_single('node', array_pop($dataset_nids));
    return entity_metadata_wrapper('node', $dataset_node);
  }

  /**
   * Test title.
   *
   * @depends testDatasetCount
   */
  public function testTitle($dataset) {
    $this->assertEquals('TEST - State Workforce by Generation (2011-2015)', $dataset->title->value());
  }

  /**
   * Test dataset count.
   *
   * @depends testDatasetCount
   */
  public function testTags($dataset) {
    $tags_expected = array(
      "demographics",
      "socioeconomic",
      "workforce",
    );

    foreach ($dataset->field_tags->value() as $tag) {
      $this->assertContains($tag->name, $tags_expected, $tag->name . ' keyword was not expected!');
      // Remove the processed tag from the expected values array.
      $key = array_search($tag->name, $tags_expected);
      if ($key !== FALSE) {
        unset($tags_expected[$key]);
      }
    }

    // Make sure that all the expected tags were found.
    $this->assertEmpty($tags_expected, 'Some expected keywords were not found.');
  }

  /**
   * Test identifer.
   *
   * @depends testDatasetCount
   */
  public function testIdentifer($dataset) {
    $this->assertEquals("95f8eac4-fd1f-4b35-8472-5c87e9425dfa", $dataset->uuid->value());
  }

  /**
   * Test Contact.
   *
   * @depends testDatasetCount
   */
  public function testContact($dataset) {
    $this->assertEquals("Stefanie Gray", $dataset->field_contact_name->value());
    $this->assertEquals("stefanie@nucivic.com", $dataset->field_contact_email->value());
  }

  /**
   * Test Temporal Coverage.
   *
   * @depends testDatasetCount
   */
  public function testTemporal($dataset) {
    $value = new DateTime("2011-01-01 00:00:00");
    $value2 = new DateTime("2015-01-01 00:00:00");
    $this->assertEquals($value->getTimestamp(), $dataset->field_temporal_coverage->value->value());
    $this->assertEquals($value2->getTimestamp(), $dataset->field_temporal_coverage->value2->value());
  }

  /**
   * Test resources.
   *
   * @depends testDatasetCount
   */
  public function testResources($dataset) {
    $expected_resources = array(
      'TEST - Workforce By Generation (2011-2015)' => 'http://demo.getdkan.com/sites/default/files/GenChart_0_0.csv',
      'TEST - Retirements (2011 - 2015)' => 'http://demo.getdkan.com/sites/default/files/retirements_0.csv',
      'TEST - Retirements: Eligible vs. Actual' => 'http://demo.getdkan.com/sites/default/files/2015EligibleVsActual.csv',
    );

    $dataset_resources = $this->getDatasetResources($dataset);

    $this->assertEquals($expected_resources, $dataset_resources);
  }

  /**
   * Test resources body format.
   *
   * @depends testDatasetCount
   */
  public function testResourcesBodyFormat($dataset) {
    $expected_resources = array(
      'TEST - Workforce By Generation (2011-2015)' => 'html',
      'TEST - Retirements (2011 - 2015)' => 'html',
      'TEST - Retirements: Eligible vs. Actual' => 'html',
    );

    $dataset_resources = $this->getDatasetResourcesFormat($dataset);

    $this->assertEquals($expected_resources, $dataset_resources);
  }

  /**
   * Test Metadata Source.
   *
   * @depends testDatasetCount
   */
  public function testMetadataSources($dataset) {
    if (!module_exists('dkan_dataset_metadata_source')) {
      $this->markTestSkipped('dkan_dataset_metadata_source module is not available.');
    }
    else {
      // This should never be empty as it is set from the cached file during the
      // harvest.
      // Title.
      $this->assertEquals($dataset->field_metadata_sources->title->value(),
        'ISO-19115 Metadata for Wye_2015-03-18T20-20-53');

      // Schema name.
      $this->assertEquals($dataset->field_metadata_sources->field_metadata_schema->name->value(),
        'ISO 19115-2');

      // File
      // TODO better way to test this?
      $this->assertNotNull($dataset->field_metadata_sources->field_metadata_file->value());
    }
  }

  /**
   * Test related content.
   *
   * @depends testDatasetCount
   */
  public function testRelatedContent($dataset) {
    $this->assertEmpty($dataset->field_related_content->value());
  }

  /**
   * Test Moderation state.
   *
   * @depends testDatasetCount
   */
  public function testModerationState($dataset) {
    if (!module_exists('dkan_workflow')) {
      $this->markTestSkipped('dkan_workflow module is not available.');
    }
    else {
      $this->markTestIncomplete('Test for moderation status missing.');
    }
  }

  /**
   * The imported dataset status should be 1 for published.
   *
   * @depends testDatasetCount
   */
  public function testStatus($dataset) {
    $this->assertEquals('1', $dataset->status->value());
  }

  /**
   * Test Groups.
   *
   * @depends testDatasetCount
   */
  public function testGroups($dataset) {
    $expected_groups = array('TEST - State Economic Council');

    // Check that the dataset has the expected groups.
    $dataset_groups = $this->getNodeGroups($dataset);
    $this->assertEquals($expected_groups, array_values($dataset_groups));

    // Check that all resources associated with the dataset have the same groups
    // as the dataset.
    foreach ($dataset->field_resources->getIterator() as $delta => $resource) {
      $resource_groups = $this->getNodeGroups($resource);
      $this->assertEquals($expected_groups, array_values($resource_groups));
    }

    // Append the dataset groups in the list of content that was created and
    // need to be deleted after test is completed.
    $this->createdNodes = array_merge($this->createdNodes, array_keys($dataset_groups));
  }

  /**
   * {@inheritdoc}
   */
  protected function tearDown() {
    // Delete all nodes that were created during the test and are not handled by
    // migrations.
    node_delete_multiple(array_unique($this->createdNodes));
    // Empty values.
    $this->createdNodes = array();
  }

  /**
   * {@inheritdoc}
   */
  public static function tearDownAfterClass() {
    // Clean all harvest migrations data from the test site. Since the Original
    // and Alternative test source are the same harvest source but with
    // different data we only need to clean one of them.
    $source = self::getOriginalTestSource();
    $source->getCacheDir(TRUE);
    dkan_harvest_rollback_sources(array($source));
    dkan_harvest_deregister_sources(array($source));

    // Clean enabled modules.
    module_disable(array('dkan_harvest_test'));
  }

  /**
   * Test Harvest Source.
   */
  public static function getOriginalTestSource() {
    return new HarvestSourceDataJsonStub(__DIR__ . '/data/dkan_harvest_datajson_test_original.json');
  }

  /**
   * Helper function to get the first node id harvested by the source.
   */
  private function getTestDatasetNid($source) {
    $migration = dkan_harvest_get_migration($source);

    if ($migration) {
      $query = $migration->getMap()->getConnection()->select($migration->getMap()->getMapTable(), 'map')
        ->fields('map')
        ->condition("needs_update", MigrateMap::STATUS_IMPORTED, '=');
      $result = $query->execute();

      $return = array();

      foreach ($result as $record) {
        if (isset($record->destid1)) {
          array_push($return, $record->destid1);
        }
      }
      return $return;
    }
  }

  /**
   * Returns an array with the list of resources associated with the dataset.
   */
  private function getDatasetResources($dataset) {
    $resources = array();

    foreach ($dataset->field_resources->getIterator() as $delta => $resource) {
      $remote_file = $resource->field_link_remote_file->value();
      $resources[$resource->title->value()] = $remote_file['uri'];
    }

    return $resources;
  }

  /**
   * Returns array of resources (with format info) associated with the dataset.
   */
  private function getDatasetResourcesFormat($dataset) {
    $resources = array();

    foreach ($dataset->field_resources->getIterator() as $delta => $resource) {
      $body = $resource->body->value();
      $resources[$resource->title->value()] = $body['format'];
    }

    return $resources;
  }

  /**
   * Returns an array with the list of groups associated with the dataset.
   */
  private function getNodeGroups($node) {
    $groups = array();

    foreach ($node->og_group_ref->getIterator() as $delta => $group) {
      $groups[$group->getIdentifier()] = $group->title->value();
    }

    return $groups;
  }

}
