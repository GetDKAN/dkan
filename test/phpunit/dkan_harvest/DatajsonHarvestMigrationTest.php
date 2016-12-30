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
   * Track nodes created during a test run that are not handled by migrations.
   *
   * @var createdNodes
   */
  private $createdNodes = array();

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
   * Simulate a harvest of a source with unchanged content.
   *
   * Harvest the same source with the same content. Make sure that:
   * - the dataset record in the migration map is not updated
   * - The global node count have not changed (No content is leaked).
   */
  public function testHarvestSourceUnchanged() {
    // We want to make sure the dataset record in the migration map did not
    // change. Collect various harvest migration data before running the
    // migration again.
    $migrationOld = dkan_harvest_get_migration(self::getOriginalTestSource());
    $migrationOldMap = $this->getMapTableFromMigration($migrationOld);
    $migrationOldLog = $this->getLogTableFromMigration($migrationOld);
    $globalDatasetCountOld = $this->getGlobalNodeCount();

    /*
     * Tests for the initial log table status.
     */
    // Since the harvest was run only once. We should have exactly one record
    // in the database.
    $this->assertEquals(1, count($migrationOldLog));

    // We are interested in comparing only the Nth and the Nth - 1 record from
    // the log table.
    // - "created" record should have decreased by 1.
    // - "unchanged" record should have increased by 1.
    // - Every other record should be the same.
    $migrationOldLogLast = end($migrationOldLog);
    $this->assertEquals(1, $migrationOldLogLast->created);

    // Nothing else should have changed.
    foreach (array('updated', 'failed', 'orphaned', 'unchanged') as $property) {
      $this->assertEquals(0, $migrationOldLogLast->{$property});
    }

    // Rerun the harvest without changing the source data.
    // Harvest cache the test source.
    dkan_harvest_cache_sources(array(self::getOriginalTestSource()));
    // Harvest Migration of the test data.
    dkan_harvest_migrate_sources(array(self::getOriginalTestSource()));

    $migrationNew = dkan_harvest_get_migration(self::getOriginalTestSource());
    $migrationNewMap = $this->getMapTableFromMigration($migrationNew);
    $migrationNewLog = $this->getLogTableFromMigration($migrationNew);
    $globalDatasetCountNew = $this->getGlobalNodeCount();

    $importedCount = $migrationNew->getMap()->importedCount();
    $this->assertEquals($importedCount, '1');

    $migrationNewMap = $this->getMapTableFromMigration($migrationNew);

    $this->assertEquals($migrationOldMap, $migrationNewMap);
    $this->assertEquals($globalDatasetCountOld, $globalDatasetCountNew);

    /*
     * Map table evolution.
     */
    // The log table should have a new recod by now.
    $this->assertEquals(count($migrationNewLog), count($migrationOldLog) + 1);

    /*
     * Log table evolution.
     */
    // The log table should have exactly one additional record by now.
    $this->assertEquals(count($migrationNewLog),
      count($migrationOldLog) + 1);

    // We are interested in comparing only the Nth and the Nth - 1 record from
    // the log table.
    // - "created" record should have decreased by 1.
    // - "unchanged" record should have increased by 1.
    // - Every other record should be the same.
    $migrationOldLogLast = end($migrationOldLog);
    $migrationNewLogLast = end($migrationNewLog);
    $this->assertEquals($migrationNewLogLast->created + 1,
      $migrationOldLogLast->created);
    $this->assertEquals($migrationNewLogLast->unchanged - 1,
      $migrationOldLogLast->unchanged);

    // Nothing else should have changed.
    foreach (array('updated', 'failed', 'orphaned') as $property) {
      $this->assertEquals($migrationNewLogLast->{$property},
        $migrationOldLogLast->{$property});
    }
  }

  /**
   * Simulate a harvest of a source with updated content.
   *
   * Harvest the same source but with different content. Make sure that:
   * - the dataset record in the harvest source migration map is updated.
   * - the dataset record in the harvest source migration log table is updated.
   * - The dataset update time is greated.
   * - The global node count have not changed (No content is leaked).
   */
  public function testHarvestSourceAlternative() {
    // Get the current values.
    $migrationOld = dkan_harvest_get_migration(self::getOriginalTestSource());
    $migrationOldMap = $this->getMapTableFromMigration($migrationOld);
    $migrationOldLog = $this->getLogTableFromMigration($migrationOld);
    $migrationOldMessage = $this->getMessageTableFromMigration($migrationOld);
    $globalDatasetCountOld = $this->getGlobalNodeCount();

    // We track the last time a record (ie. a dataset) is updated by a
    // timestamp. For less havier harvests like the example used for the test
    // it can take less then one second to run multiple takes with different
    // data and that can mess with the tests. To workaround that we introduce a
    // artificial 1 second delay.
    sleep(1);

    // Rerun the harvest (cache + migration) with the alternative source. the
    // source XML docs. Harvest cache the test source.
    dkan_harvest_cache_sources(array(self::getAlternativeTestSource()));
    dkan_harvest_migrate_sources(array(self::getAlternativeTestSource()));

    $migrationAlternative = dkan_harvest_get_migration(self::getAlternativeTestSource());
    $migrationAlternativeMap = $this->getMapTableFromMigration($migrationAlternative);
    $migrationAlternativeLog = $this->getLogTableFromMigration($migrationAlternative);
    $migrationAlternativeMessage = $this->getMessageTableFromMigration($migrationAlternative);

    // Get the map table post alternative source harvest.
    $migrationAlternativeMap = $this->getMapTableFromMigration($migrationAlternative);

    // The number of managed datasets record should stay the same.
    $this->assertEquals(count($migrationAlternativeMap), '1');
    // The number of nodes as a hole should be increased by 1 because a new
    // group should be created.
    $globalDatasetCountAlternative = $this->getGlobalNodeCount();
    $this->assertEquals($globalDatasetCountOld, $globalDatasetCountAlternative);

    // The harvest source map table should not be same after harvesting a
    // different content.
    $this->assertNotEquals($migrationOldMap, $migrationAlternativeMap);

    // Specifically check that the last_imported in the new alternative dataset
    // record is greater then the previous old dataset record.
    foreach (array_keys($migrationAlternativeMap) as $index) {
      $this->assertGreaterThan($migrationOldMap[$index]->last_imported,
        $migrationAlternativeMap[$index]->last_imported);
    }

    /*
     * Test Log table evolution.
     */
    // The log table should have exactly one additional record by now.
    $this->assertEquals(count($migrationAlternativeLog),
      count($migrationOldLog) + 1);
    // We are interested in comparing only the Nth and the Nth - 1 record from
    // the log table.
    // - "orphaned" record should have increased by 1.
    // - "unchanged" record should have decreased by 1.
    // - Every other record should be the same.
    $migrationOldLogLast = end($migrationOldLog);
    $migrationAlternativeLogLast = end($migrationAlternativeLog);

    $this->assertEquals($migrationAlternativeLogLast->updated,
      $migrationOldLogLast->updated + 1);
    $this->assertEquals($migrationAlternativeLogLast->unchanged,
      $migrationOldLogLast->unchanged - 1);
    // Nothing else should have changed.
    foreach (array('created', 'failed', 'orphaned') as $property) {
      $this->assertEquals($migrationOldLogLast->{$property},
        $migrationAlternativeLogLast->{$property});
    }

    /*
     * Test message table.
     */
    // We don't expect any new messages from this test. The old and new message
    // table should be the same.
    $this->assertEquals($migrationOldMessage, $migrationAlternativeMessage);
  }

  /**
   * Simulate a harvest of a source with faulty content.
   *
   * Harvest the same source but with different content. Make sure that:
   * - the dataset record in the harvest source migration map is updated.
   * - the dataset record in the harvest source migration log table is updated.
   * - The dataset update time is greated.
   * - The global node count have not changed (No content is leaked).
   */
  public function testHarvestSourceError() {
    // Get the current values.
    $migrationOld = dkan_harvest_get_migration(self::getOriginalTestSource());
    $migrationOldMap = $this->getMapTableFromMigration($migrationOld);
    $migrationOldLog = $this->getLogTableFromMigration($migrationOld);
    $migrationOldMessage = $this->getMessageTableFromMigration($migrationOld);

    $globalDatasetCountOld = $this->getGlobalNodeCount();

    // We track the last time a record (ie. a dataset) is updated by a
    // timestamp. For less havier harvests like the example used for the test
    // it can take less then one second to run multiple takes with different
    // data and that can mess with the tests. To workaround that we introduce a
    // artificial 1 second delay.
    sleep(1);

    // Rerun the harvest (cache + migration) with the error source. the
    // source XML docs. Harvest cache the test source.
    dkan_harvest_cache_sources(array(self::getErrorTestSource()));
    dkan_harvest_migrate_sources(array(self::getErrorTestSource()));

    $migrationError = dkan_harvest_get_migration(self::getErrorTestSource());
    $migrationErrorMap = $this->getMapTableFromMigration($migrationError);
    $migrationErrorLog = $this->getLogTableFromMigration($migrationError);
    $migrationErrorMessage = $this->getMessageTableFromMigration($migrationError);

    // Get the map table post error source harvest.
    $migrationErrorMap = $this->getMapTableFromMigration($migrationError);

    // The number of managed datasets record should stay the same.
    $this->assertEquals(count($migrationErrorMap), count($migrationOldMap));

    // The number of nodes as a hole should not have changed.
    $globalDatasetCountError = $this->getGlobalNodeCount();
    $this->assertEquals($globalDatasetCountOld, $globalDatasetCountError);

    // The harvest source map table should not be same after harvesting a
    // different content.
    $this->assertNotEquals($migrationOldMap, $migrationErrorMap);

    // Specifically check that the last_imported in the new error dataset
    // record is greater then the previous old dataset record.
    foreach (array_keys($migrationErrorMap) as $index) {
      $this->assertGreaterThan($migrationOldMap[$index]->last_imported,
        $migrationErrorMap[$index]->last_imported);
    }

    /*
     * Test Log table evolution.
     */
    // The log table should have exactly one additional record by now.
    $this->assertEquals(count($migrationErrorLog),
      count($migrationOldLog) + 1);
    // We are interested in comparing only the Nth and the Nth - 1 record from
    // the log table.
    // - "updated" record should have decreased by 1.
    // - "failed" record should have increased by 1.
    // - Every other record should be the same.
    $migrationOldLogLast = end($migrationOldLog);
    $migrationErrorLogLast = end($migrationErrorLog);

    $this->assertEquals($migrationErrorLogLast->updated,
      $migrationOldLogLast->updated - 1);
    $this->assertEquals($migrationErrorLogLast->failed,
      $migrationOldLogLast->failed + 1);
    // Nothing else should have changed.
    foreach (array('created', 'unchanged', 'orphaned') as $property) {
      $this->assertEquals($migrationOldLogLast->{$property},
        $migrationErrorLogLast->{$property});
    }

    /*
     * Test message table.
     */
    // AFter harvesting a erroneous source, it is expected to have an error
    // logged into the messsage table.
    $this->assertNotEquals($migrationOldMessage, $migrationErrorMessage);
    // We should at least have one more message.
    $this->assertGreaterThan(count($migrationOldMessage),
      count($migrationErrorMessage));

    // Get the new messages. One of them should be an error message.
    $messages_diff = array_diff_key($migrationErrorMessage, $migrationOldMessage);
    $errors_level = array();
    foreach ($messages_diff as $msgid => $message) {
      $errors_level[] = $message->level;
    }
    $this->assertContains(Migration::MESSAGE_ERROR, $errors_level);
  }

  /**
   * Simulate a harvest of an empty source after harvesting the faulty source.
   *
   * Harvest the same source but with emtpy content. Make sure that:
   * - the dataset record in the harvest source migration map is updated.
   * - the dataset record in the harvest source migration log table is updated.
   * - The dataset update time is greated.
   * - The global node count have not changed (No content is leaked).
   */
  public function testHarvestSourceEmpty() {
    // Get the current values.
    $migrationOld = dkan_harvest_get_migration(self::getOriginalTestSource());
    $migrationOldMap = $this->getMapTableFromMigration($migrationOld);
    $migrationOldLog = $this->getLogTableFromMigration($migrationOld);
    $migrationOldMessage = $this->getMessageTableFromMigration($migrationOld);

    $globalDatasetCountOld = $this->getGlobalNodeCount();

    // We track the last time a record (ie. a dataset) is updated by a
    // timestamp. For less havier harvests like the example used for the test
    // it can take less then one second to run multiple takes with different
    // data and that can mess with the tests. To workaround that we introduce a
    // artificial 1 second delay.
    sleep(1);

    // Rerun the harvest (cache + migration) with the empty source. the
    // source XML docs. Harvest cache the test source.
    dkan_harvest_cache_sources(array(self::getEmptyTestSource()));
    dkan_harvest_migrate_sources(array(self::getEmptyTestSource()));

    $migrationEmpty = dkan_harvest_get_migration(self::getEmptyTestSource());
    $migrationEmptyMap = $this->getMapTableFromMigration($migrationEmpty);
    $migrationEmptyLog = $this->getLogTableFromMigration($migrationEmpty);
    $migrationEmptyMessage = $this->getMessageTableFromMigration($migrationEmpty);

    // Get the map table post empty source harvest.
    $migrationEmptyMap = $this->getMapTableFromMigration($migrationEmpty);

    // The number of managed datasets record should have decrised by one (the
    // faulty item part of the previous import should've been cleaned by now).
    $this->assertEquals(count($migrationEmptyMap), count($migrationOldMap) - 1);

    // The number of nodes as a hole should not have changed.
    $globalDatasetCountEmpty = $this->getGlobalNodeCount();
    $this->assertEquals($globalDatasetCountOld, $globalDatasetCountEmpty);

    // The harvest source map table should not be same after harvesting a
    // different content.
    $this->assertNotEquals($migrationOldMap, $migrationEmptyMap);

    // For empty source no update happened so the last_imported column should
    // match.
    foreach (array_keys($migrationEmptyMap) as $index) {
      $this->assertEquals($migrationOldMap[$index]->last_imported,
        $migrationEmptyMap[$index]->last_imported);
    }

    /*
     * Test Log table evolution.
     */
    // The log table should have exactly one additional record by now.
    $this->assertEquals(count($migrationEmptyLog),
      count($migrationOldLog) + 1);
    // We are interested in comparing only the Nth and the Nth - 1 record from
    // the log table.
    // - "orphaned" record should have increased by 1.
    // - "failed" record should be decreased by 1.
    // - Every other record should be the same.
    $migrationOldLogLast = end($migrationOldLog);
    $migrationEmptyLogLast = end($migrationEmptyLog);

    $this->assertEquals($migrationEmptyLogLast->orphaned,
      $migrationOldLogLast->orphaned);
    $this->assertEquals($migrationEmptyLogLast->failed,
      $migrationOldLogLast->failed - 1);
    // Nothing else should have changed.
    foreach (array('created', 'updated', 'unchanged') as $property) {
      $this->assertEquals($migrationOldLogLast->{$property},
        $migrationEmptyLogLast->{$property});
    }

    /*
     * Test message table.
     */
    // We expect one new message from this test when harvesting the empty
    // source.
    $this->assertNotEquals($migrationOldMessage, $migrationEmptyMessage);
    $this->assertEquals(count($migrationOldMessage) + 1, count($migrationEmptyMessage));
  }

  /**
   * Test harvest source "Zombi" entries.
   *
   * Test for a specific case where a dataset from the source is corrupted and
   * fails to import. If the harvest source removes the faulty dataset no
   * record should be left on the map table.
   */
  public function testHarvestSourceZombi() {

    // Clean the harvest migration data from the source.
    dkan_harvest_rollback_sources(array(self::getErrorTestSource()));
    dkan_harvest_deregister_sources(array(self::getErrorTestSource()));

    // Harvest the faulty source.
    dkan_harvest_cache_sources(array(self::getErrorTestSource()));
    dkan_harvest_migrate_sources(array(self::getErrorTestSource()));

    $migrationError = dkan_harvest_get_migration(self::getErrorTestSource());
    $migrationErrorMap = $this->getMapTableFromMigration($migrationError);
    $migrationErrorLog = $this->getLogTableFromMigration($migrationError);
    $migrationErrorLog = $this->getLogTableFromMigration($migrationError);
    $migrationErrorMessage = $this->getMessageTableFromMigration($migrationError);

    // Harvest the faulty source.
    dkan_harvest_cache_sources(array(self::getErrorTestSource()));
    dkan_harvest_migrate_sources(array(self::getErrorTestSource()));

    // Harvest the empty source.
    dkan_harvest_cache_sources(array(self::getEmptyTestSource()));
    dkan_harvest_migrate_sources(array(self::getEmptyTestSource()));

    $migrationEmpty = dkan_harvest_get_migration(self::getEmptyTestSource());
    $migrationEmptyMap = $this->getMapTableFromMigration($migrationEmpty);
    $migrationEmptyLog = $this->getLogTableFromMigration($migrationEmpty);
    $migrationEmptyMessage = $this->getMessageTableFromMigration($migrationEmpty);

    $values = $migrationEmpty->getMap()->lookupMapTable(HarvestMigrateSQLMap::STATUS_FAILED, NULL, NULL, NULL, NULL);
    $this->assertEmpty($migrationEmptyMap);

    /*
     * Test message table.
     */
    // Harvesting the empty source will add a new error message.
    $this->assertNotEquals($migrationErrorMessage, $migrationEmptyMessage);
    $this->assertEquals(count($migrationErrorMessage) + 1, count($migrationEmptyMessage));
  }

  /**
   * The harvest migration should not remove old log messages after a harvest.
   */
  public function testHarvestSourceMessagesAppend() {

    // Clean the harvest migration data from the source.
    dkan_harvest_rollback_sources(array(self::getErrorTestSource()));
    dkan_harvest_deregister_sources(array(self::getErrorTestSource()));

    // Harvest the faulty source.
    dkan_harvest_cache_sources(array(self::getErrorTestSource()));
    dkan_harvest_migrate_sources(array(self::getErrorTestSource()));

    $migrationError = dkan_harvest_get_migration(self::getErrorTestSource());
    $migrationErrorMessage = $this->getMessageTableFromMigration($migrationError);

    // Now that we have error messages, re-harvest the faulty source to get new
    // messages for the same dataset sources. To force harvest migration to
    // re-harvest an unchanged source we pass the 'dkan_harvest_skip_hash'
    // option.
    $options = array(
      'skiphash' => TRUE,
    );
    dkan_harvest_cache_sources(array(self::getErrorTestSource()));
    dkan_harvest_migrate_sources(array(self::getErrorTestSource()), $options);

    $migrationErrorAfter = dkan_harvest_get_migration(self::getErrorTestSource());
    $migrationErrorAfterMessage = $this->getMessageTableFromMigration($migrationError);

    /*
     * Test message table.
     */
    // We don't expect any new messages from this test. The old and new message
    // table should be the same.
    $this->assertNotEquals($migrationErrorMessage, $migrationErrorAfterMessage);
    // We should record the same errors. So if the first harvest yelded 2
    // errors the second one should have 4.
    $this->assertEquals(count($migrationErrorMessage) * 2, count($migrationErrorAfterMessage));
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
   * Test groups update.
   *
   * @depends testDatasetCount
   *
   * When a dataset group is updated the following should happen:
   *   - The dataset should be associated with the new groups.
   *   - All the resources associated with the dataset should also be modified
   *     with the new group.
   */
  public function testGroupsUpdate($dataset) {

    // Append the current dataset groups in the list of content that was created
    // and need to be deleted after test is completed.
    $dataset_groups = $this->getNodeGroups($dataset);
    $this->createdNodes = array_merge($this->createdNodes, array_keys($dataset_groups));

    // Check that the number of groups in the dataset is '1'.
    $this->assertEquals(count($dataset_groups), '1');

    // Rerun the harvest (cache + migration) with the group updated source.
    dkan_harvest_cache_sources(array(self::getGroupUpdatedTestSource()));
    dkan_harvest_migrate_sources(array(self::getGroupUpdatedTestSource()));

    // Get updated dataset.
    $dataset_nids = $this->getTestDatasetNid(self::getGroupUpdatedTestSource());
    $dataset_node = entity_load_single('node', array_pop($dataset_nids));
    $dataset = entity_metadata_wrapper('node', $dataset_node);

    // Groups should've changed. Append the dataset groups in the list of
    // content that was created and need to be deleted after test is completed.
    $this->createdNodes = array_merge($this->createdNodes, array_keys($dataset_groups));

    // Check that the dataset got the groups updated.
    $expected_groups = array('TEST - State Economic Council Updated');
    $dataset_groups = $this->getNodeGroups($dataset);
    $this->assertEquals($expected_groups, array_values($dataset_groups));

    // Check that the number of groups associated with the dataset is still '1'.
    $this->assertEquals(count($dataset_groups), '1');

    // Check that the group was updated on all resources.
    foreach ($dataset->field_resources->getIterator() as $delta => $resource) {
      $resource_groups = $this->getNodeGroups($resource);
      $this->assertEquals($expected_groups, array_values($resource_groups));
    }
  }

  /**
   * Test remote file support for files behind redirects.
   *
   * Ticket: https://jira.govdelivery.com/browse/CIVIC-4501
   */
  public function testResourceRedirect() {
    // Clean the harvest migration data from the source.
    dkan_harvest_rollback_sources(array(self::getOriginalTestSource()));
    dkan_harvest_deregister_sources(array(self::getOriginalTestSource()));

    dkan_harvest_cache_sources(array(self::getResourceWithRedirects()));
    dkan_harvest_migrate_sources(array(self::getResourceWithRedirects()));

    // Get updated dataset.
    $dataset_nids = $this->getTestDatasetNid(self::getResourceWithRedirects());
    $dataset_node = entity_load_single('node', array_pop($dataset_nids));

    // One resource should exists.
    $this->assertEquals(count($dataset_node->field_resources[LANGUAGE_NONE]), 1);

    // Load the resource.
    $resource = array_pop($dataset_node->field_resources[LANGUAGE_NONE]);
    $resource_emw = entity_metadata_wrapper('node', $resource['target_id']);

    $this->assertNotNull($resource_emw->field_link_remote_file);
  }

  /**
   * Check Error logging for the harvest.
   *
   * Ticket: https://jira.govdelivery.com/browse/CIVIC-4498
   *
   * Make sure harvest error from the base HarvestMigration class are logged.
   * This probably should always be the last test in the test suite since it
   * drops some taxonomies which will make following tests fail.
   */
  public function testHarvestError() {

    // Clean the harvest migration data from the source.
    dkan_harvest_rollback_sources(array(self::getOriginalTestSource()));
    dkan_harvest_deregister_sources(array(self::getOriginalTestSource()));

    // Delete the format vocabulary.
    $vocab_format = taxonomy_vocabulary_machine_name_load('format');
    if ($vocab_format) {
      taxonomy_vocabulary_delete($vocab_format->vid);
    }

    // Running the harvest should generate an error.
    dkan_harvest_cache_sources(array(self::getOriginalTestSource()));
    dkan_harvest_migrate_sources(array(self::getOriginalTestSource()));

    $migrationError = dkan_harvest_get_migration(self::getOriginalTestSource());
    $migrationErrorMessages = $this->getMessageTableFromMigration($migrationError);

    $messages = array_filter($migrationErrorMessages, function ($message) {
      return $message->level == 1;
    });

    $messages = array_map(function ($message) {
      return $message->message;
    }, $messages);

    $this->assertContains("Cannot get taxonomy csv (format vocabulary).", $messages);

    // Similar test for when dkan_dataset_metadata_source is available.
    if (!module_exists('dkan_dataset_metadata_source')) {
      $this->markTestSkipped("dkan_dataset_metadata_source module does not exists.");
    }
    else {
      // Clean the harvest migration data from the source.
      dkan_harvest_rollback_sources(array(self::getOriginalTestSource()));
      dkan_harvest_deregister_sources(array(self::getOriginalTestSource()));

      // Delete the format vocabulary.
      $vocab_format = taxonomy_vocabulary_machine_name_load('extended_metadata_schema');
      if ($vocab_format) {
        taxonomy_vocabulary_delete($vocab_format->vid);
      }

      // Running the harvest should generate an error.
      dkan_harvest_cache_sources(array(self::getOriginalTestSource()));
      dkan_harvest_migrate_sources(array(self::getOriginalTestSource()));

      $migrationError = dkan_harvest_get_migration(self::getOriginalTestSource());
      $migrationErrorMessages = $this->getMessageTableFromMigration($migrationError);

      $messages = array_filter($migrationErrorMessages, function ($message) {
        return $message->level == 1;
      });

      $messages = array_map(function ($message) {
        return $message->message;
      }, $messages);

      $this->assertContains("Cannot get taxonomy csv (format vocabulary).", $messages);
    }
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
   * Test Harvest Source.
   */
  public static function getAlternativeTestSource() {
    return new HarvestSourceDataJsonStub(__DIR__ . '/data/dkan_harvest_datajson_test_alternative.json');
  }

  /**
   * Test Harvest Source.
   */
  public static function getGroupUpdatedTestSource() {
    return new HarvestSourceDataJsonStub(__DIR__ . '/data/dkan_harvest_datajson_test_group_updated.json');
  }

  /**
   * Test Harvest Source.
   */
  public static function getErrorTestSource() {
    return new HarvestSourceDataJsonStub(__DIR__ . '/data/dkan_harvest_datajson_test_error.json');
  }

  /**
   * Test Harvest Source.
   */
  public static function getEmptyTestSource() {
    return new HarvestSourceDataJsonStub(__DIR__ . '/data/dkan_harvest_datajson_test_empty.json');
  }

  /**
   * Test Harvest Source.
   */
  public static function getNoResourceTestSource() {
    return new HarvestSourceDataJsonStub(__DIR__ . '/data/dkan_harvest_datajson_test_no_resources.json');
  }

  /**
   * Test Harvest Source.
   */
  public static function getResourceWithRedirects() {
    return new HarvestSourceDataJsonStub(__DIR__ . '/data/dkan_harvest_datajson_test_redirects.json');
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
   * Helper method to get a harvest migration map table from the migration.
   *
   * @param HarvestMigration $migration
   *   Harvest Migration object.
   *
   * @return array
   *   Array of records of the harvest source migration map table keyed
   *   by destid1.
   */
  private function getMapTableFromMigration(HarvestMigration $migration) {
    $map = $migration->getMap();
    $result = $map->getConnection()->select($map->getMapTable(), 'map')
      ->fields('map')
      ->execute();

    return $result->fetchAllAssoc('sourceid1');
  }

  /**
   * Helper method to get a harvest migration messages table from the migration.
   *
   * @param HarvestMigration $migration
   *   Harvest Migration object.
   *
   * @return array
   *   Array of records of the harvest source migration messages table keyed
   *   by destid1.
   */
  private function getMessageTableFromMigration(HarvestMigration $migration) {
    $map = $migration->getMap();
    $result = $map->getConnection()->select($map->getMessageTable(), 'message')
      ->fields('message')
      ->execute();

    return $result->fetchAllAssoc('msgid');
  }

  /**
   * Helper method to get a harvest migration log table from the migration.
   *
   * @param HarvestMigration $migration
   *   Harvest Migration object.
   *
   * @return array
   *   Array of records of the harvest source migration log table keyed
   *   by destid1.
   */
  private function getLogTableFromMigration(HarvestMigration $migration) {
    $map = $migration->getMap();
    $result = $map->getConnection()->select($map->getLogTable(), 'log')
      ->fields('log')
      ->execute();

    return $result->fetchAllAssoc('mlid');
  }

  /**
   * Return the count of all the nodes.
   */
  private function getGlobalNodeCount() {
    $query = "SELECT COUNT(*) amount FROM {node} n";
    $result = db_query($query)->fetch();
    return $result->amount;
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
