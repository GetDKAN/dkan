<?php

namespace Drupal\Tests\datastore\Unit\Controller;

use Dkan\Datastore\Resource;
use Drupal\common\DatasetInfo;
use Drupal\Core\Cache\Context\CacheContextsManager;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Database\Driver\sqlite\Connection as SqliteConnection;
use MockChain\Options;
use Drupal\datastore\Service;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use MockChain\Chain;
use Drupal\datastore\Controller\QueryController;
use Drupal\datastore\Storage\SqliteDatabaseTable;
use Drupal\metastore\MetastoreApiResponse;
use Drupal\metastore\NodeWrapper\Data;
use Drupal\metastore\NodeWrapper\NodeDataFactory;
use Drupal\metastore\Storage\DataFactory;
use Ilbee\CSVResponse\CSVResponse as CsvResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 *
 */
class QueryControllerTest extends TestCase {

  protected function setUp() {
    parent::setUp();
    // Set cache services
    $options = (new Options)
      ->add('cache_contexts_manager', CacheContextsManager::class)
      ->index(0);
    $chain = (new Chain($this))
      ->add(ContainerInterface::class, 'get', $options)
      ->add(CacheContextsManager::class, 'assertValidTokens', TRUE);
    \Drupal::setContainer($chain->getMock());
  }

  public function testQueryJson() {
    $data = json_encode([
      "resources" => [
        [
          "id" => "2",
          "alias" => "t",
        ],
      ],
    ]);

    $container = $this->getQueryContainer($data)->getMock();
    $webServiceApi = QueryController::create($container);
    $request = $this->mockRequest($data);
    $result = $webServiceApi->query($request);

    $this->assertTrue($result instanceof JsonResponse);
    $this->assertEquals(200, $result->getStatusCode());
  }

  // Make sure nothing fails with no resources.
  public function testQueryJsonNoResources() {
    $data = json_encode([
      "properties" => [
        [
          "resource" => "t",
          "property" => "field",
        ],
      ],
    ]);

    $container = $this->getQueryContainer($data)->getMock();
    $webServiceApi = QueryController::create($container);
    $request = $this->mockRequest($data);
    $result = $webServiceApi->query($request);

    $this->assertTrue($result instanceof JsonResponse);
    $this->assertEquals(200, $result->getStatusCode());
  }

  public function testQueryInvalid() {
    $data = json_encode([
      "resources" => "nope",
    ]);

    $container = $this->getQueryContainer($data)->getMock();
    $webServiceApi = QueryController::create($container);
    $request = $this->mockRequest($data);
    $result = $webServiceApi->query($request);

    $this->assertTrue($result instanceof JsonResponse);
    $this->assertEquals(400, $result->getStatusCode());
  }


  public function testResourceQueryInvalidJson() {
    $data = "{[";

    $container = $this->getQueryContainer($data)->getMock();
    $webServiceApi = QueryController::create($container);
    $request = $this->mockRequest($data);
    $result = $webServiceApi->queryResource("2", $request);

    $this->assertTrue($result instanceof JsonResponse);
    $this->assertEquals(400, $result->getStatusCode());
  }

  public function testResourceQueryInvalidQuery() {
    $data = json_encode([
      "conditions" => "nope",
    ]);
    $container = $this->getQueryContainer($data)->getMock();
    $webServiceApi = QueryController::create($container);
    $request = $this->mockRequest($data);
    $result = $webServiceApi->queryResource("2", $request);

    $this->assertTrue($result instanceof JsonResponse);
    $this->assertEquals(400, $result->getStatusCode());
  }

  public function testResourceQueryWithJoin() {
    $data = json_encode([
      "joins" => [
        "resource" => "t",
        "condition" => "t.field1 = s.field1",
      ],
    ]);
    $container = $this->getQueryContainer($data)->getMock();
    $webServiceApi = QueryController::create($container);
    $request = $this->mockRequest($data);
    $result = $webServiceApi->queryResource("2", $request);

    $this->assertTrue($result instanceof JsonResponse);
    $this->assertEquals(400, $result->getStatusCode());
  }

  /**
   *
   */
  public function testResourceQueryJson() {
    $data = json_encode([
      "results" => TRUE,
    ]);

    $container = $this->getQueryContainer($data)->getMock();
    $webServiceApi = QueryController::create($container);
    $request = $this->mockRequest($data);
    $result = $webServiceApi->queryResource("2", $request);

    $this->assertTrue($result instanceof JsonResponse);
    $this->assertEquals(200, $result->getStatusCode());
  }

  /**
   *
   */
  public function testQueryCsv() {
    $data = json_encode([
      "resources" => [
        [
          "id" => "2",
          "alias" => "t",
        ],
      ],
      "format" => "csv",
    ]);

    $container = $this->getQueryContainer($data)->getMock();
    $webServiceApi = QueryController::create($container);
    $request = $this->mockRequest($data);
    $result = $webServiceApi->query($request);

    $this->assertTrue($result instanceof CsvResponse);
    $this->assertEquals(200, $result->getStatusCode());

    $csv = explode("\n", $result->getContent());
    $this->assertEquals('state,year', $csv[0]);
    $this->assertEquals('Alabama,2010', $csv[1]);
    $this->assertContains('data.csv', $result->headers->get('Content-Disposition'));
  }

  /**
   *
   */
  public function testResourceQueryCsv() {
    $data = json_encode([
      "results" => TRUE,
      "format" => "csv",
    ]);

    $container = $this->getQueryContainer($data)->getMock();
    $webServiceApi = QueryController::create($container);
    $request = $this->mockRequest($data);
    $result = $webServiceApi->queryResource("2", $request);

    $this->assertTrue($result instanceof CsvResponse);
    $this->assertEquals(200, $result->getStatusCode());
  }

  public function testQuerySchema() {
    $container = $this->getQueryContainer()->getMock();
    $webServiceApi = QueryController::create($container);
    $result = $webServiceApi->querySchema();

    $this->assertTrue($result instanceof JsonResponse);
    $this->assertEquals(200, $result->getStatusCode());
    $this->assertContains("json-schema.org", $result->getContent());
  }

  /**
   *
   */
  public function testDistributionIndexWrongIdentifier() {
    $data = json_encode([
      "results" => TRUE,
    ]);
    $info = ['notice' => 'Not found'];

    $container = $this->getQueryContainer($data, "GET", $info)->getMock();
    $webServiceApi = QueryController::create($container);
    $request = $this->mockRequest($data);
    $result = $webServiceApi->queryDatasetResource("2", "0", $request);

    $this->assertTrue($result instanceof JsonResponse);
    $this->assertEquals(400, $result->getStatusCode());
  }

  /**
   *
   */
  public function testDistributionIndexWrongIndex() {
    $data = json_encode([
      "results" => TRUE,
    ]);
    $info['latest_revision']['distributions'][0]['distribution_uuid'] = '123';

    $container = $this->getQueryContainer($data, "GET", $info)->getMock();
    $webServiceApi = QueryController::create($container);
    $request = $this->mockRequest($data);
    $result = $webServiceApi->queryDatasetResource("2", "1", $request);

    $this->assertTrue($result instanceof JsonResponse);
    $this->assertEquals(400, $result->getStatusCode());
  }

  /**
   *
   */
  public function testDistributionIndex() {
    $data = json_encode([
      "results" => TRUE,
    ]);
    $info['latest_revision']['distributions'][0]['distribution_uuid'] = '123';

    $container = $this->getQueryContainer($data, "GET", $info)->getMock();
    $webServiceApi = QueryController::create($container);
    $request = $this->mockRequest($data);
    $result = $webServiceApi->queryDatasetResource("2", "0", $request);

    $this->assertTrue($result instanceof JsonResponse);
    $this->assertEquals(200, $result->getStatusCode());
  }

  /**
   *
   */
  public function testQueryCsvCacheHeaders() {
    $data = json_encode([
      "resources" => [
        [
          "id" => "2",
          "alias" => "t",
        ],
      ],
      "format" => "csv",
    ]);

    // Create a container with caching turned on.
    $containerChain = $this->getQueryContainer($data)
      ->add(Container::class, 'has', TRUE)
      ->add(ConfigFactoryInterface::class, 'get', ImmutableConfig::class)
      ->add(ImmutableConfig::class, 'get', 600);
    $container = $containerChain->getMock();
    \Drupal::setContainer($container);

    // CSV. Caching on.
    $webServiceApi = QueryController::create($container);
    $request = $this->mockRequest($data);
    $response = $webServiceApi->query($request);
    $headers = $response->headers;

    $this->assertEquals('text/csv', $headers->get('content-type'));
    $this->assertEquals('max-age=600, public', $headers->get('cache-control'));
    $this->assertNotEmpty($headers->get('last-modified'));

    // Turn caching off.
    $containerChain->add(ImmutableConfig::class, 'get', 0)->getMock();
    $container = $containerChain->getMock();
    \Drupal::setContainer($container);

    // CSV. No caching.
    $webServiceApi = QueryController::create($container);
    $request = $this->mockRequest($data);
    $response = $webServiceApi->query($request);
    $headers = $response->headers;

    $this->assertEquals('text/csv', $headers->get('content-type'));
    $this->assertEquals('no-cache, private', $headers->get('cache-control'));
    $this->assertEmpty($headers->get('vary'));
    $this->assertEmpty($headers->get('last-modified'));
  }

  private function getQueryContainer($data = '', string $method = "POST", array $info = []) {
    if ($method == "GET") {
      $request = Request::create("http://example.com?$data", $method);
    }
    else {
      $request = Request::create("http://example.com", $method, [], [], [], [], $data);
    }

    $options = (new Options())
      ->add("dkan.metastore.storage", DataFactory::class)
      ->add("dkan.datastore.service", Service::class)
      ->add("dkan.common.dataset_info", DatasetInfo::class)
      ->add('config.factory', ConfigFactoryInterface::class)
      ->add('dkan.metastore.metastore_item_factory', NodeDataFactory::class)
      ->add('dkan.metastore.api_response', MetastoreApiResponse::class)
      ->index(0);

    $chain = (new Chain($this))
      ->add(Container::class, "get", $options)
      ->add(DatasetInfo::class, "gather", $info)
      ->add(MetastoreApiResponse::class, 'getMetastoreItemFactory', NodeDataFactory::class)
      ->add(MetastoreApiResponse::class, 'addReferenceDependencies', NULL)
      ->add(NodeDataFactory::class, 'getInstance', Data::class)
      ->add(Data::class, 'getCacheContexts', ['url'])
      ->add(Data::class, 'getCacheTags', ['node:1'])
      ->add(Data::class, 'getCacheMaxAge', 0)
      ->add(Service::class, "getQueryStorageMap", ['t' => $this->mockDatastoreTable()])
      ->add(ConfigFactoryInterface::class, 'get', ImmutableConfig::class)
      ->add(ImmutableConfig::class, 'get', 500);

    return $chain;
  }

  /**
   * We just test POST requests; logic for other methods is tested elsewhere.
   *
   * @param string $data
   *   Request body.
   */
  public function mockRequest($data = '') {
    return Request::create("http://example.com", 'POST', [], [], [], [], $data);
  }

  /**
   * Create a mock datastore table in memory with SQLite.
   *
   * The table will be based on states_with_dupes.csv, which contains the
   * columns "record_number", "state" and "year". The record_number column
   * is in ascending order but skips many numbers, and both other columns
   * contain duplicate values.
   *
   * @return \Drupal\common\Storage\DatabaseTableInterface
   *   A database table storage class useable for datastore queries.
   */
  public function mockDatastoreTable() {
    $connection = new SqliteConnection(new \PDO('sqlite::memory:'), []);
    $connection->query('CREATE TABLE `datastore_2` (`record_number` INTEGER NOT NULL, state TEXT, year INT);');

    $sampleData = [];
    $fp = fopen(__DIR__ . '/../../../data/states_with_dupes.csv', 'rb');
    while (!feof($fp)) {
      $sampleData[] = fgetcsv($fp);
    }
    foreach ($sampleData as $row) {
      $connection->query("INSERT INTO `datastore_2` VALUES ($row[0], '$row[1]', $row[2]);");
    }

    $storage = new SqliteDatabaseTable($connection, new Resource("2", "data.csv", "text/csv"));
    $storage->setSchema([
      'fields' => [
        'record_number' => ['type' => 'int', 'not null' => TRUE],
        'state' => ['type' => 'text'],
        'year' => ['type' => 'int'],
      ],
    ]);
    return $storage;
  }

}
