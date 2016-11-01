<?php
/**
 * @file
 */

/**
 *
 */
class DkanHarvestDataJsonTest extends PHPUnit_Framework_TestCase {

  /**
   * {@inheritdoc}
   */
  public static function setUpBeforeClass() {
    // Harvest cache the test source.
    dkan_harvest_cache_sources(array(self::getTestSource()));

    // Harvest Migration of the test data.
    dkan_harvest_migrate_sources(array(self::getTestSource()));
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
  }

  public function testDKANHarvestDataJsonModifiers() {
    $source = self::getTestSource();
    $data = drupal_json_decode(file_get_contents(__DIR__ . '/data/dkan_harvest_datajson_test_filters.json'));
    $cache = dkan_harvest_datajson_cache_pod_v1_1_json($data, $source, microtime());
    $uuid = reset(array_keys($cache->getSaved()));
    $node = reset(array_values($cache->getSaved()));
    $identifier = dkan_harvest_datajson_prepare_item_id($uuid);
    $dataset_file = implode('/', array($source->getCacheDir(), $identifier));
    $dataset = drupal_json_decode(file_get_contents(drupal_realpath($dataset_file)));
    $this->assertEquals($node['title'], 'Wisconsin Polling Places TEST');
    $this->assertEquals($dataset['awesomekey'], 'politics');
    $this->assertEquals($dataset['publisher']['name'], 'nucivic');
  }

  /**
   * @covers dkan_harvest_datajson_prepare_item_id().
   */
  public function testDKANHarvestDataJsonPrepareItemId()
  {
    $url = 'http://example.com/what';
    $dir = dkan_harvest_datajson_prepare_item_id($url);
    $this->assertEquals($dir, 'what');

    $url = 'http://example.com/what/now';
    $dir = dkan_harvest_datajson_prepare_item_id($url);
    $this->assertEquals($dir, 'now');

    $url = 'http://example.com';
    $dir = dkan_harvest_datajson_prepare_item_id($url);
    $this->assertEquals($dir, '');
  }


  /**
   * {@inheritdoc}
   */
  protected function tearDown() {
  }

  /**
   * {@inheritdoc}
   */
  public static function tearDownAfterClass() {
    dkan_harvest_rollback_sources(array(self::getTestSource()));
    dkan_harvest_deregister_sources(array(self::getTestSource()));
  }

  /**
   * Test Harvest Source.
   */
  public static function getTestSource() {
    $source = new HarvestSourceDataJsonStub(__DIR__ . '/data/dkan_harvest_datajson_test_filters.json');
    $source->filters = array('keyword' => array('election'));
    $source->excludes = array('keyword' => array('politics'));
    $source->defaults = array('awesomekey' => array('politics'));
    $source->overrides = array('publisher.name' => array('nucivic'));
    return $source;
  }
}
