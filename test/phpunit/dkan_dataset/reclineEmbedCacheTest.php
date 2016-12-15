<?php

/**
 * Tests for ReclineEmbedCacheTest class.
 *
 * @class ReclineEmbedCacheTest
 */
class ReclineEmbedCacheTest extends PHPUnit_Framework_TestCase {

  protected $cacheSettings;

  /**
   * {@inheritdoc}
   */
  public static function setUpBeforeClass() {
    self::addResource();
  }

  /**
   * Save cache settings in a temporary variable.
   */
  protected function saveCacheSettings() {
    $this->cacheSettings = array(
      "cache" => variable_get("cache"),
      "page_cache_maximum_age" => variable_get("page_cache_maximum_age"),
      "cache_lifetime" => variable_get("cache_lifetime"),
    );
  }

  /**
   * Restore cache settings
   */
  public function restoreCacheSettings()
  {
    if($this->cacheSettings) {
      variable_set('cache', $this->cacheSettings['cache']);
      variable_set('page_cache_maximum_age', $this->cacheSettings['page_cache_maximum_age']);
      variable_set('cache_lifetime', $this->cacheSettings['cache_lifetime']);
      $this->cacheSettings = null;
    }
  }

  /**
   * Disable cache
   */
  public function disableCache()
  {
    $this->saveCacheSettings();
    variable_set('cache', FALSE);
    variable_set('page_cache_maximum_age', NULL);
    variable_set('cache_lifetime', NULL);
  }

  /**
   * Enable cache
   */
  public function enableCache()
  {
    $this->saveCacheSettings();
    variable_set('cache', TRUE);
    variable_set('page_cache_maximum_age', 300);
    variable_set('cache_lifetime', 180);
  }

  /**
   * Test recline embed cache enabled.
   */
  public function testReclineEmbedCacheEnabled() {
    global $base_url;
    $this->enableCache();
    $node = array_values(entity_uuid_load('node', array('3a05eb8c-3733-11e6-ad41-9e71128cae77')))[0];
    $result = drupal_http_request($base_url . '/node/' . $node->nid . '/recline-embed');
    $headers = $result->headers;
    $this->assertEquals($headers['cache-control'], 'public, max-age=300');
    $this->restoreCacheSettings();
  }

  /**
   * Test recline embed cache disabled.
   */
  public function testReclineEmbedCacheDisabled() {
    global $base_url;
    $this->disableCache();
    $node = array_values(entity_uuid_load('node', array('3a05eb8c-3733-11e6-ad41-9e71128cae77')))[0];
    $result = drupal_http_request($base_url . '/node/' . $node->nid . '/recline-embed');
    $headers = $result->headers;
    $this->assertEquals($headers['cache-control'], 'no-cache, must-revalidate, post-check=0, pre-check=0');
    $this->restoreCacheSettings();
  }

  /**
   * Add a resource to test.
   */
  private static function addResource() {
    // Create resource.
    $filename = 'gold_prices_states.csv';
    $node = new stdClass();
    $node->title = 'Resource Embed Cache Test';
    $node->type = 'resource';
    $node->uid = 1;
    $node->uuid = '3a05eb8c-3733-11e6-ad41-9e71128cae77';
    $node->language = 'und';
    $path = join(DIRECTORY_SEPARATOR, array(__DIR__, 'files', $filename));
    $file = file_save_data(file_get_contents($path), 'public://' . $filename);
    $node->field_upload[LANGUAGE_NONE][0] = (array)$file;
    node_save($node);
  }

  public static function tearDownAfterClass() {
    entity_uuid_delete('node', '3a05eb8c-3733-11e6-ad41-9e71128cae77');
  }

}