<?php
/**
 * @file
 * Base phpunit tests for HarvestSourceType class.
 */
class DkanDatastoreAPITest extends \PHPUnit_Framework_TestCase {

  /**
   * {@inheritdoc}
   */
  public static function setUpBeforeClass() {
    $resources = self::getResources();
    foreach ($resources as $resource) {
      self::addResource($resource);
    }

  }

  /**
   * Retrieves an keyed array of resources.
   */
  private static function getResources() {
    $resources = array(
      'gold_prices' => array(
        'filename' => 'gold_prices.csv',
        'title' => 'Gold Prices',
        'uuid'=>'3a05eb8c-3733-11e6-ac61-9e71128cae77'
      ),
      'gold_prices_states' => array(
        'filename' => 'gold_prices_states.csv',
        'title' => 'Gold Prices States',
        'uuid'=>'3a05eb8c-3733-11e6-ac61-9e71128cae78'
      ),
      'polling_places' => array(
        'filename' => 'polling_places.csv',
        'title' => 'Polling Places',
        'uuid'=>'3a05eb9c-3733-11e6-ac61-9e71128cae79'
      ),
    );
    return $resources;
  }

  /**
   * Given a resource key retrieves a uuid.
   */
  private static function getUUID($key, $resources) {
    if(array_key_exists($key, $resources)) {
      return $resources[$key]['uuid'];
    } else {
      throw new \Exception('Resource is not defined');
    }

  }

  /**
   * Add a resource to test.
   */
  private static function addResource($resource) {

    // Create resource.
    $filename = $resource['filename'];
    $node = new stdClass();
    $node->title = $resource['title'];
    $node->type = 'resource';
    $node->uid = 1;
    $node->uuid = $resource['uuid'];
    $node->language = 'und';
    $path = join(DIRECTORY_SEPARATOR, array(__DIR__, 'files', $filename));
    $file = file_save_data(file_get_contents($path), 'public://' . $filename);
    $node->field_upload[LANGUAGE_NONE][0] = (array)$file;
    node_save($node);

    // Import it to the datastore.
    $importerId = 'dkan_file';
    $source = feeds_source($importerId, $node->nid);
    $config = array(
      'process_in_background' => TRUE,
    );
    $source->importer->addConfig($config);

    while (FEEDS_BATCH_COMPLETE != $source->import());
  }

  /**
   * Teardown function.
   */
  public static function tearDownAfterClass() {
    $resources = self::getResources();
    foreach ($resources as $resource) {
      entity_uuid_delete('node', array($resource['uuid']));
    }
  }

  /**
   * Query test.
   */
  public function test_dkan_datstore_api_query() {
    $params = array(
      'resource_id' => array(
        'polling_places' => self::getUUID('polling_places', self::getResources()),
      ),
      'limit' => 1000,
      'query' => 'City'
    );
    $params = _dkan_datastore_api_get_params($params);
    $result = _dkan_datastore_api_query($params);
    $this->assertEquals($result['result']->total, 3);
  }

  /**
   * Filter test.
   */
  public function test_dkan_datstore_api_filters() {
    $params = array(
      'resource_id' => array(
        'gold_prices' => self::getUUID('gold_prices', self::getResources()),
      ),
      'limit' => 1000,
      'filters' => array(
        'date' => '1950-02-01'
      )
    );
    $params = _dkan_datastore_api_get_params($params);
    $result = _dkan_datastore_api_query($params);
    $this->assertEquals($result['result']->total, 1);
  }

  /**
   * Offset test.
   */
  public function test_dkan_datstore_api_offset() {
    $params = array(
      'resource_id' => array(
        'gold_prices_states' => self::getUUID('gold_prices_states', self::getResources()),
      ),
      'limit' => 1,
      'offset' => 1,
    );
    $params = _dkan_datastore_api_get_params($params);
    $result = _dkan_datastore_api_query($params);
    $this->assertEquals($result['result']->records[0]->state_id, 2);
  }

  /**
   * Limit test.
   */
  public function test_dkan_datstore_api_limit() {
    $params = array(
      'resource_id' => array(
        'gold_prices_states' => self::getUUID('gold_prices_states', self::getResources()),
      ),
      'limit' => 1
    );
    $params = _dkan_datastore_api_get_params($params);
    $result = _dkan_datastore_api_query($params);
    $this->assertEquals(count($result['result']->records), 1);
  }

  /**
   * Fields test.
   */
  public function test_dkan_datstore_api_fields() {
    $params = array(
      'resource_id' => array(
        'gold_prices_states' => self::getUUID('gold_prices_states', self::getResources()),
      ),
      'fields' => array('name'),
      'limit' => 1,
    );
    $params = _dkan_datastore_api_get_params($params);
    $result = _dkan_datastore_api_query($params);
    $this->assertEquals(count((array)$result['result']->records[0]), 1);
  }

  /**
   * Sort test.
   */
  public function test_dkan_datstore_api_sort() {
    $params = array(
      'resource_id' => array(
        'gold_prices_states' => self::getUUID('gold_prices_states', self::getResources()),
      ),
      'sort' => array('state_id' => 'desc'),
      'limit' => 1
    );
    $params = _dkan_datastore_api_get_params($params);
    $result = _dkan_datastore_api_query($params);
    $this->assertEquals($result['result']->records[0]->state_id, 5);
  }

  /**
   * Group by test.
   */
  public function test_dkan_datstore_api_group_by() {
    $params = array(
      'resource_id' => array(
        'gold_prices' => self::getUUID('gold_prices', self::getResources()),
      ),
      'limit' => 1000,
      'group_by' => array('price')
    );
    $params = _dkan_datastore_api_get_params($params);
    $result = _dkan_datastore_api_query($params);
    $this->assertEquals($result['result']->total, 582);
  }

  /**
   * Join test.
   */
  public function test_dkan_datstore_api_join() {
    $params = array(
      'resource_id' => array(
        'gold_prices_states' => self::getUUID('gold_prices_states', self::getResources()),
        'gold_prices' => self::getUUID('gold_prices', self::getResources()),
      ),
      'join' => array(
        'gold_prices_states' => 'state_id',
        'gold_prices' => 'state_id',
      ),
      'limit' => 5,
    );
    $params = _dkan_datastore_api_get_params($params);
    $result = _dkan_datastore_api_query($params);
    $this->assertObjectHasAttribute('name', $result['result']->records[0]);
    $this->assertObjectHasAttribute('price', $result['result']->records[0]);
  }

  /**
   * Join with filters test.
   */
  public function test_dkan_datstore_api_join_with_filters() {
    $params = array(
      'resource_id' => array(
        'gold_prices_states' => self::getUUID('gold_prices_states', self::getResources()),
        'gold_prices' => self::getUUID('gold_prices', self::getResources()),
      ),
      'join' => array(
        'gold_prices_states' => 'state_id',
        'gold_prices' => 'state_id',
      ),
      'limit' => 5,
      'filters' => array(
        'date' => '1950-02-01'
      )
    );
    $params = _dkan_datastore_api_get_params($params);
    $result = _dkan_datastore_api_query($params);

    $this->assertObjectHasAttribute('name', $result['result']->records[0]);
    $this->assertObjectHasAttribute('price', $result['result']->records[0]);
    $this->assertEquals('Alabama', $result['result']->records[0]->name);
    $this->assertEquals($result['result']->total, 1);
  }


  /**
   * Multiquery test.
   */
  public function test_dkan_datstore_api_multiquery() {
    $queries = array(
      'my_query' => array(
        'resource_id' => array(
          'states' => '3a05eb8c-3733-11e6-ac61-9e71128cae78',
        ),
        'limit' => 5,
      ),
      'my_query1' => array(
        'resource_id' => array(
          'gold_prices' => '3a05eb8c-3733-11e6-ac61-9e71128cae77'
        ),
        'limit' => 5,
      )
    );
    $result = _dkan_datastore_api_multiple_query($queries);
    $this->assertArrayHasKey('my_query', $result);
    $this->assertArrayHasKey('my_query1', $result);
    $this->assertEquals(count($result['my_query']['result']->records), 5);
    $this->assertEquals(count($result['my_query1']['result']->records), 5);
  }

  /**
   * Test aggregations
   */
  public function test_dkan_datstore_api_aggregations() {
    $aggregations = array('sum', 'avg', 'min', 'max', 'count');
    $expect = array(
      'sum' => 219726,
      'avg' => 293,
      'min' => 34,
      'max' => 1780,
      'count' => 748,
    );
    foreach ($aggregations as $agg) {
      $params = array(
        'resource_id' => array(
          'gold_prices' => self::getUUID('gold_prices', self::getResources()),
        ),
        'limit' => 1000
      );
      $params[$agg] = 'price';
      $params = _dkan_datastore_api_get_params($params);
      $result = _dkan_datastore_api_query($params);
      $this->assertEquals(floor($result['result']->records[0]->{$agg.'_price'}) , $expect[$agg]);
    }

  }
}