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
class DatajsonHarvestMigrationScenariosTest extends PHPUnit_Framework_TestCase {

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
    // We need this module for the testResourceRedirect test.
    module_enable(array('dkan_harvest_test'));
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
  }

  /**
   * Simulate a harvest of a source with unchanged content.
   *
   * Harvest the same source with the same content. Make sure that:
   * - the dataset record in the migration map is not updated
   * - The global node count have not changed (No content is leaked).
   */
  public function testHarvestSourceUnchanged() {
    $source = self::getOriginalTestSource();
    // Harvest cache the test source.
    dkan_harvest_cache_sources(array($source));
    // Harvest Migration of the test data.
    dkan_harvest_migrate_sources(array($source));

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
    $source = self::getOriginalTestSource();
    // Harvest cache the test source.
    dkan_harvest_cache_sources(array($source));
    // Harvest Migration of the test data.
    dkan_harvest_migrate_sources(array($source));

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

    // Nothing else should have changed.
    $log_expected = array(
      'created' => 0,
      'updated' => 1,
      'failed' => 0,
      'orphaned' => 0,
      'unchanged' => 0,
    );
    foreach ($log_expected as $property => $value) {
      $this->assertEquals($value, $migrationAlternativeLogLast->{$property});
    }

    // Test message table.
    // We don't expect any new error messages from this test.
    $this->assertEquals(0, count($migrationAlternativeMessage));
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
    // Run the harvest (cache + migration) with the error source. the
    // source XML docs. Harvest cache the test source.
    dkan_harvest_cache_sources(array(self::getErrorTestSource()));
    dkan_harvest_migrate_sources(array(self::getErrorTestSource()));

    $migrationError = dkan_harvest_get_migration(self::getErrorTestSource());
    $migrationErrorMap = $this->getMapTableFromMigration($migrationError);
    $migrationErrorLog = $this->getLogTableFromMigration($migrationError);
    $migrationErrorMessage = $this->getMessageTableFromMigration($migrationError);

    // Assert The number of managed datasets.
    $this->assertEquals(1, count($migrationErrorMap));

    // Test Log table.
    // The log table should have exactly one additional record by now.
    $this->assertEquals(1, count($migrationErrorLog));

    // Check the log table.
    // - "updated" record should have decreased by 1.
    // - "failed" record should have increased by 1.
    // - Every other record should be the same.
    $migrationErrorLogLast = end($migrationErrorLog);

    $log_expected = array(
      'created' => 0,
      'updated' => 0,
      'failed' => 1,
      'orphaned' => 0,
      'unchanged' => 0,
    );
    // Nothing else should have changed.
    foreach ($log_expected as $property => $expected_value) {
      $this->assertEquals($expected_value,
        $migrationErrorLogLast->{$property});
    }

    // Test message table.
    // AFter harvesting a erroneous source, it is expected to have an error
    // logged into the messsage table.
    // We should at least have one more message.
    $this->assertGreaterThan(0, count($migrationErrorMessage));

    // Make sure one of the messages is an error message.
    $errors_level = array_map(function ($message) {
      return $message->level;
    },
    $migrationErrorMessage);
    $this->assertContains(Migration::MESSAGE_ERROR, $errors_level);
  }

  /**
   * Test a harvest of an empty source.
   *
   * Harvest the same source but with emtpy content. Make sure that:
   * - the dataset record in the harvest source migration map is updated.
   * - the dataset record in the harvest source migration log table is updated.
   * - The dataset update time is greated.
   * - The global node count have not changed (No content is leaked).
   */
  public function testHarvestSourceEmpty() {
    $globalDatasetCountOld = $this->getGlobalNodeCount();

    // Rerun the harvest (cache + migration) with the empty source. the
    // source XML docs. Harvest cache the test source.
    dkan_harvest_cache_sources(array(self::getEmptyTestSource()));
    dkan_harvest_migrate_sources(array(self::getEmptyTestSource()));

    $migrationEmpty = dkan_harvest_get_migration(self::getEmptyTestSource());
    $migrationEmptyMap = $this->getMapTableFromMigration($migrationEmpty);
    $migrationEmptyLog = $this->getLogTableFromMigration($migrationEmpty);
    $migrationEmptyMessage = $this->getMessageTableFromMigration($migrationEmpty);

    // No dataset imported. Map Table should be empty.
    $this->assertEquals(0, count($migrationEmptyMap));

    // The number of nodes as a whole should not have changed.
    $this->assertEquals($globalDatasetCountOld, $this->getGlobalNodeCount());

    // Log table assertions.
    $this->assertEquals(1, count($migrationEmptyLog));

    $migrationEmptyLogLast = end($migrationEmptyLog);
    $log_expected = array(
      'created' => 0,
      'updated' => 0,
      'failed' => 0,
      'orphaned' => 0,
      'unchanged' => 0,
    );

    foreach ($log_expected as $property => $expected_value) {
      $this->assertEquals($expected_value, $migrationEmptyLogLast->{$property});
    }

    // Message table assertions.
    // We expect one new message from this test when harvesting the empty
    // source.
    $this->assertEquals(1, count($migrationEmptyMessage));
    $messageEntry = end($migrationEmptyMessage);
    $this->assertEquals("Items to import is 0. Looks like source is missing. All the content previously harvested will be unpublished.", $messageEntry->message);
  }

  /**
   * Test harvest source "Zombi" entries.
   *
   * Test for a specific case where a dataset from the source is corrupted and
   * fails to import. If the harvest source removes the faulty dataset no
   * record should be left on the map table.
   */
  public function testHarvestSourceZombi() {
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
    // Harvest the faulty source.
    dkan_harvest_cache_sources(array(self::getErrorTestSource()));
    dkan_harvest_migrate_sources(array(self::getErrorTestSource()));

    $migrationError = dkan_harvest_get_migration(self::getErrorTestSource());
    $migrationErrorMessage = $this->getMessageTableFromMigration($migrationError);

    // Now that we have error messages, re-harvest the faulty source to get new
    // messages for the same dataset sources. To force harvest migration to
    // re-harvest an unchanged source we pass the 'skiphash'
    // option.
    $options = array(
      'skiphash' => TRUE,
    );
    dkan_harvest_cache_sources(array(self::getErrorTestSource()));
    dkan_harvest_migrate_sources(array(self::getErrorTestSource()), $options);

    $migrationErrorAfter = dkan_harvest_get_migration(self::getErrorTestSource());
    $migrationErrorAfterMessage = $this->getMessageTableFromMigration($migrationError);

    // Message table assertions.
    // We don't expect any new messages from this test. The old and new message
    // table should be the same.
    $this->assertNotEquals($migrationErrorMessage, $migrationErrorAfterMessage);
    // We should record the same errors. So if the first harvest yelded 2
    // errors the second one should have 4.
    $this->assertEquals(count($migrationErrorMessage) * 2, count($migrationErrorAfterMessage));
  }

  /**
   * Test groups update.
   *
   * When a dataset group is updated the following should happen:
   *   - The dataset should be associated with the new groups.
   *   - All the resources associated with the dataset should also be modified
   *     with the new group.
   */
  public function testGroupsUpdate() {
    // Harvest the test source.
    $source = self::getOriginalTestSource();
    // Harvest cache the test source.
    dkan_harvest_cache_sources(array($source));
    // Harvest Migration of the test data.
    dkan_harvest_migrate_sources(array($source));
    $dataset_nid = end($this->getTestDatasetNid($source));
    $dataset = entity_metadata_wrapper('node', $dataset_nid);

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
    // Harvest a source that have resources behind redirects.
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
    return;
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
    // Clean all harvest migrations data from the test site. Since the Original
    // and Alternative test source are the same harvest source but with
    // different data we only need to clean one of them.
    $source = self::getOriginalTestSource();
    $source->getCacheDir(TRUE);
    dkan_harvest_rollback_sources(array($source));
    dkan_harvest_deregister_sources(array($source));

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
   * Test Harvest Source.
   */
  public static function getResourceSchemeless() {
    return new HarvestSourceDataJsonStub(__DIR__ . '/data/dkan_harvest_datajson_test_schemeless_resource.json');
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
