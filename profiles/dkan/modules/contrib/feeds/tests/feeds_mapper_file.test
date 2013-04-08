<?php

/**
 * @file
 * Test case for Filefield mapper mappers/filefield.inc.
 */

/**
 * Class for testing Feeds file mapper.
 *
 * @todo Add a test for enclosures using a local file that is
 *   a) in place and that
 *   b) needs to be copied from one location to another.
 */
class FeedsMapperFileTestCase extends FeedsMapperTestCase {
  public static function getInfo() {
    return array(
      'name' => 'Mapper: File field',
      'description' => 'Test Feeds Mapper support for file fields. <strong>Requires SimplePie library</strong>.',
      'group' => 'Feeds',
    );
  }

  /**
   * Basic test loading a single entry CSV file.
   */
  public function test() {
    // If this is unset (or FALSE) http_request.inc will use curl, and will generate a 404
    // for this feel url provided by feeds_tests. However, if feeds_tests was enabled in your
    // site before running the test, it will work fine. Since it is truly screwy, lets just
    // force it to use drupal_http_request for this test case.
    variable_set('feeds_never_use_curl', TRUE);
    variable_set('clean_url', TRUE);

    // Only download simplepie if the plugin doesn't already exist somewhere.
    // People running tests locally might have it.
    if (!feeds_simplepie_exists()) {
      $this->downloadExtractSimplePie('1.3');
      $this->assertTrue(feeds_simplepie_exists());
      // Reset all the caches!
      $this->resetAll();
    }
    $typename = $this->createContentType(array(), array('files' => 'file'));

    // 1) Test mapping remote resources to file field.

    // Create importer configuration.
    $this->createImporterConfiguration();
    $this->setPlugin('syndication', 'FeedsSimplePieParser');
    $this->setSettings('syndication', 'FeedsNodeProcessor', array('bundle' => $typename));
    $this->addMappings('syndication', array(
      0 => array(
        'source' => 'title',
        'target' => 'title'
      ),
      1 => array(
        'source' => 'timestamp',
        'target' => 'created'
      ),
      2 => array(
        'source' => 'enclosures',
        'target' => 'field_files'
      ),
    ));
    $nid = $this->createFeedNode('syndication', $GLOBALS['base_url'] . '/testing/feeds/flickr.xml');
    $this->assertText('Created 5 nodes');

    $files = $this->_testFiles();
    $entities = db_select('feeds_item')
      ->fields('feeds_item', array('entity_id'))
      ->condition('id', 'syndication')
      ->execute();
    foreach ($entities as $entity) {
      $this->drupalGet('node/' . $entity->entity_id . '/edit');
      $f = new FeedsEnclosure(array_shift($files), NULL);
      $this->assertText($f->getLocalValue());
    }

    // 2) Test mapping local resources to file field.

    // Copy directory of files, CSV file expects them in public://images, point
    // file field to a 'resources' directory. Feeds should copy files from
    // images/ to resources/ on import.
    $this->copyDir($this->absolutePath() . '/tests/feeds/assets', 'public://images');
    $edit = array(
      'instance[settings][file_directory]' => 'resources',
    );
    $this->drupalPost('admin/structure/types/manage/' . $typename . '/fields/field_files', $edit, t('Save settings'));

    // Create a CSV importer configuration.
    $this->createImporterConfiguration('Node import from CSV', 'node');
    $this->setPlugin('node', 'FeedsCSVParser');
    $this->setSettings('node', 'FeedsNodeProcessor', array('bundle' => $typename));
    $this->setSettings('node', NULL, array('content_type' => ''));
    $this->addMappings('node', array(
      0 => array(
        'source' => 'title',
        'target' => 'title'
      ),
      1 => array(
        'source' => 'file',
        'target' => 'field_files'
      ),
    ));

    // Import.
    $edit = array(
      'feeds[FeedsHTTPFetcher][source]' => $GLOBALS['base_url'] . '/testing/feeds/files.csv',
    );
    $this->drupalPost('import/node', $edit, 'Import');
    $this->assertText('Created 5 nodes');

    // Assert: files should be in resources/.
    $files = $this->_testFiles();
    $entities = db_select('feeds_item')
      ->fields('feeds_item', array('entity_id'))
      ->condition('id', 'node')
      ->execute();
    foreach ($entities as $entity) {
      $this->drupalGet('node/' . $entity->entity_id . '/edit');
      $f = new FeedsEnclosure(array_shift($files), NULL);
      $this->assertRaw('resources/' . $f->getUrlEncodedValue());
    }

    // 3) Test mapping of local resources, this time leave files in place.
    $this->drupalPost('import/node/delete-items', array(), 'Delete');
    // Setting the fields file directory to images will make copying files
    // obsolete.
    $edit = array(
      'instance[settings][file_directory]' => 'images',
    );
    $this->drupalPost('admin/structure/types/manage/' . $typename . '/fields/field_files', $edit, t('Save settings'));
    $edit = array(
      'feeds[FeedsHTTPFetcher][source]' => $GLOBALS['base_url'] . '/testing/feeds/files.csv',
    );
    $this->drupalPost('import/node', $edit, 'Import');
    $this->assertText('Created 5 nodes');

    // Assert: files should be in images/ now.
    $files = $this->_testFiles();
    $entities = db_select('feeds_item')
      ->fields('feeds_item', array('entity_id'))
      ->condition('id', 'node')
      ->execute();
    foreach ($entities as $entity) {
      $this->drupalGet('node/' . $entity->entity_id . '/edit');
      $f = new FeedsEnclosure(array_shift($files), NULL);
      $this->assertRaw('images/' . $f->getUrlEncodedValue());
    }

    // Deleting all imported items will delete the files from the images/ dir.
    // @todo: for some reason the first file does not get deleted.
//    $this->drupalPost('import/node/delete-items', array(), 'Delete');
//    foreach ($this->_testFiles() as $file) {
//      $this->assertFalse(is_file("public://images/$file"));
//    }
  }

  /**
   * Lists test files.
   */
  public function _testFiles() {
    return array('tubing.jpeg', 'foosball.jpeg', 'attersee.jpeg', 'hstreet.jpeg', 'la fayette.jpeg');
  }

  /**
   * Handle file field widgets.
   */
  public function selectFieldWidget($fied_name, $field_type) {
    if ($field_type == 'file') {
      return 'file_generic';
    }
    else {
      return parent::selectFieldWidget($fied_name, $field_type);
    }
  }
}
