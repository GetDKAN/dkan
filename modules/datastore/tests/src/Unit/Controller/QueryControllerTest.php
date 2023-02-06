<?php

namespace Drupal\Tests\datastore\Unit\Controller;

use Drupal\datastore\DatastoreResource;
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
use Drupal\datastore\Service\Query;
use Drupal\datastore\Storage\SqliteDatabaseTable;
use Drupal\metastore\MetastoreApiResponse;
use Drupal\metastore\NodeWrapper\Data;
use Drupal\metastore\NodeWrapper\NodeDataFactory;
use Drupal\metastore\Storage\DataFactory;
use Ilbee\CSVResponse\CSVResponse as CsvResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\datastore\Service\ResourceLocalizer;
use FileFetcher\FileFetcher;
use Drupal\datastore\Service\Factory\Import as ImportServiceFactory;
use Drupal\metastore\ResourceMapper;
use Drupal\Core\Queue\QueueFactory;
use Drupal\datastore\Service\Import as ImportService;
use Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Drupal\common\DataResource;

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

    $result = $this->getQueryResult($data);

    $this->assertTrue($result instanceof JsonResponse);
    $this->assertEquals(200, $result->getStatusCode());
  }

  /**
   * Do some things differently to throw exception on metastore retrieval.
   */
  public function testQueryWrongIdentifier() {
    $data = json_encode([
      "results" => TRUE,
      "resources" => [
        [
          "id" => "9",
          "alias" => "t",
        ],
      ],
    ]);

    $options = (new Options())
      ->add("dkan.metastore.storage", DataFactory::class)
      ->index(0);
    $container = (new Chain($this))
      ->add(Container::class, "get", $options)
      ->add(DataFactory::class, 'getInstance', MockStorage::class)
      ->getMock();
    \Drupal::setContainer($container);

    $chain = $this->getQueryContainer($data, [], FALSE);
    $webServiceApi = QueryController::create($chain->getMock());
    $request = $this->mockRequest($data);
    $result = $webServiceApi->query($request);

    $this->assertTrue($result instanceof JsonResponse);
    $this->assertEquals(404, $result->getStatusCode());
    $this->assertStringContainsString('Error retrieving published dataset', $result->getContent());
  }

  public function testQueryRowIdProperty() {
    // Try simple string properties:
    $data = json_encode(["properties" => ["record_number", "state"]]);

    $result = $this->getQueryResult($data, "2");

    $this->assertEquals(400, $result->getStatusCode());
    $this->assertStringContainsString('The record_number property is for internal use', $result->getContent());

    // Now try with rowIds plus an arbitrary property:
    $data = json_encode(["properties" => ["state"], "rowIds" => TRUE]);

    $result = $this->getQueryResult($data, "2");

    $this->assertEquals(400, $result->getStatusCode());
    $this->assertStringContainsString('The rowIds property cannot be set to true', $result->getContent());

    // Now try with resource properties:
    $data = json_encode([
      "properties" => [
        [
          "resource" => "t",
          "property" => "state",
        ],
        [
          "resource" => "t",
          "property" => "record_number",
        ],
      ],
    ]);

    $result = $this->getQueryResult($data, "2");

    $this->assertEquals(400, $result->getStatusCode());
    $this->assertStringContainsString('The record_number property is for internal use', $result->getContent());
  }

  public function testQueryRowIdSort() {
    $data = json_encode([
      "sorts" => [
        [
          "resource" => "t",
          "property" => "state",
          "order" => "desc",
        ],
        [
          "resource" => "t",
          "property" => "record_number",
          "order" => "asc",
        ],
      ],
    ]);

    $result = $this->getQueryResult($data, "2");

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

    $result = $this->getQueryResult($data);

    $this->assertTrue($result instanceof JsonResponse);
    $this->assertEquals(200, $result->getStatusCode());
  }

  public function testQueryInvalid() {
    $data = json_encode([
      "resources" => "nope",
    ]);

    $result = $this->getQueryResult($data);

    $this->assertTrue($result instanceof JsonResponse);
    $this->assertEquals(400, $result->getStatusCode());
  }


  public function testResourceQueryInvalidJson() {
    $data = "{[";

    $result = $this->getQueryResult($data);

    $this->assertTrue($result instanceof JsonResponse);
    $this->assertEquals(400, $result->getStatusCode());
  }

  public function testResourceQueryInvalidQuery() {
    $data = json_encode([
      "conditions" => "nope",
    ]);
    $result = $this->getQueryResult($data, "2");

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
    $result = $this->getQueryResult($data, "2");

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
    $result = $this->getQueryResult($data, "2");

    $this->assertTrue($result instanceof JsonResponse);
    $this->assertEquals(200, $result->getStatusCode());
  }

  /**
   * Do some things differently to throw exception on metastore retrieval.
   */
  public function testResourceQueryWrongIdentifier() {
    $data = json_encode([
      "results" => TRUE,
    ]);

    $options = (new Options())
      ->add("dkan.metastore.storage", DataFactory::class)
      ->index(0);
    $container = (new Chain($this))
      ->add(Container::class, "get", $options)
      ->add(DataFactory::class, 'getInstance', MockStorage::class)
      ->getMock();
    \Drupal::setContainer($container);

    $chain = $this->getQueryContainer($data, [], FALSE);
    $webServiceApi = QueryController::create($chain->getMock());
    $request = $this->mockRequest($data);
    $result = $webServiceApi->queryResource("9", $request);

    $this->assertTrue($result instanceof JsonResponse);
    $this->assertEquals(404, $result->getStatusCode());
    $this->assertStringContainsString('Error retrieving published dataset', $result->getContent());
  }

  /**
   *
   */
  public function testResourceQueryJoins() {
    $data = json_encode([
      "results" => TRUE,
      "joins" => [
        [
          "resource" => "t",
          "condition" => [
            "resource" => "t",
            "property" => "record_number",
            "value" => [
              "resource" => "t",
              "property" => "record_number",
            ]
          ]
        ],
      ],
    ]);
    $result = $this->getQueryResult($data, "2");

    $this->assertTrue($result instanceof JsonResponse);
    $this->assertEquals(400, $result->getStatusCode());
    $this->assertStringContainsString('Joins are not available', $result->getContent());
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

    $result = $this->getQueryResult($data);

    $this->assertTrue($result instanceof CsvResponse);
    $this->assertEquals(200, $result->getStatusCode());

    $csv = explode("\n", $result->getContent());
    $this->assertEquals('state,year', $csv[0]);
    $this->assertEquals('Alabama,2010', $csv[1]);
    $this->assertStringContainsString('data.csv', $result->headers->get('Content-Disposition'));
  }

  private function getQueryResult($data, $id = NULL, $index = NULL, $info = []) {
    $container = $this->getQueryContainer($data, $info)->getMock();
    $webServiceApi = QueryController::create($container);
    $request = $this->mockRequest($data);
    if ($id === NULL && $index === NULL) {
      return $webServiceApi->query($request);
    }
    if ($index === NULL) {
      return $webServiceApi->queryResource($id, $request);
    }
    return $webServiceApi->queryDatasetResource($id, $index, $request);
  }

  /**
   *
   */
  public function testResourceQueryCsv() {
    $data = json_encode([
      "results" => TRUE,
      "format" => "csv",
    ]);
    $result = $this->getQueryResult($data);

    $this->assertTrue($result instanceof CsvResponse);
    $this->assertEquals(200, $result->getStatusCode());
  }

  public function testQuerySchema() {
    $container = $this->getQueryContainer()->getMock();
    $webServiceApi = QueryController::create($container);
    $result = $webServiceApi->querySchema();

    $this->assertTrue($result instanceof JsonResponse);
    $this->assertEquals(200, $result->getStatusCode());
    $this->assertStringContainsString("json-schema.org", $result->getContent());
  }

  /**
   *
   */
  public function testDistributionIndexWrongIdentifier() {
    $data = json_encode([
      "results" => TRUE,
    ]);

    $result = $this->getQueryResult($data, "2", 0);

    $this->assertTrue($result instanceof JsonResponse);
    $this->assertEquals(404, $result->getStatusCode());
  }

  /**
   *
   */
  public function testDistributionIndexWrongIndex() {
    $data = json_encode([
      "results" => TRUE,
    ]);
    $info['latest_revision']['distributions'][0]['distribution_uuid'] = '123';

    $result = $this->getQueryResult($data, "2", 1, $info);

    $this->assertTrue($result instanceof JsonResponse);
    $this->assertEquals(404, $result->getStatusCode());
  }

  /**
   *
   */
  public function testDistributionIndex() {
    $data = json_encode([
      "results" => TRUE,
    ]);
    $info['latest_revision']['distributions'][0]['distribution_uuid'] = '123';

    $result = $this->getQueryResult($data, "2", 0, $info);

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

  /**
   * Testing Get Data Dictionary Fields. rada
   */
  public function testGetDatastoreService() {
    $resource = new DataResource('http://example.org', 'text/csv');
    $chain = $this->getContainerChainForService('dkan.datastore.service')
      ->add(ResourceLocalizer::class, 'get', $resource)
      ->add(ResourceLocalizer::class, 'getResult', Result::class)
      ->add(FileFetcher::class, 'run', Result::class)
      ->add(ResourceMapper::class, 'get', $resource)
      ->add(ImportServiceFactory::class, "getInstance", ImportService::class)
      ->add(ImportService::class, "import", NULL)
      ->add(ImportService::class, "getResult", new Result())
      ->add(QueueFactory::class, "get", NULL)
      ->add(ContainerAwareEventDispatcher::class, "dispatch", NULL);

    $service = Service::create($chain->getMock());
    $result = $service->getDatastoreService;

    $this->assertTrue($result instanceof Service);

  }

  /**
   * Private.
   */
  private function getContainerChainForService($serviceName): Chain {
    $options = $this->getContainerOptionsForService($serviceName);
    return (new Chain($this))->add(Container::class, 'get', $options);
  }

  /**
   * Private.
   */
  private function getContainerOptionsForService($serviceName): Options {
    $options = (new Options())->index(0);
    $service = $this->checkService($serviceName);
    // Extract services from service arguments.
    $arguments = array_filter($service['arguments'], function ($arg) {
      return preg_match('/^@[^@]/', $arg, $matches) === 1;
    });
    foreach ($arguments as $arg) {
      // Extract service name from argument.
      $arg = str_replace("@", '', $arg);
      $argService = $this->checkService($arg);
      $class = $argService['class'];
      if ($class[0] == '\\') {
        $class = substr($class, 1);
      }
      $options->add($arg, $class);
    }
    return $options;
  }

  /**
   * Private.
   */
  private function checkService($serviceName) {
    $dkanModules = [
      'common',
      'datastore',
      'frontend',
      'harvest',
      'metastore',
    ];
    $files = [];

    foreach ($dkanModules as $dkanModule) {
      $files[] = $this->getRelativeDkanModulePath($dkanModule) . "/{$dkanModule}.services.yml";
    }
    $files[] = $this->getRelativeDrupalPath() . "/core/core.services.yml";

    foreach ($files as $file) {
      $content = Yaml::decode(file_get_contents($file));
      $services = array_keys($content['services']);
      if (in_array($serviceName, $services)) {
        $this->assertTrue(TRUE, "{$serviceName} exists in {$file}");
        return $content['services'][$serviceName];
      }
    }
    $this->assertFalse(TRUE, "{$serviceName} does not exist in DKAN or Drupal core.");
  }

  /**
   * Private.
   */
  private function getRelativeDkanModulePath($moduleName, $path = NULL) {
    if (!$path) {
      $path = $this->getRelativeDkanPath();
    }

    foreach (new \DirectoryIterator($path) as $fileInfo) {
      if ($fileInfo->isDir() && !$fileInfo->isDot()) {
        if ($fileInfo->getFilename() == $moduleName) {
          return $fileInfo->getPathname();
        }
        elseif ($fileInfo->getFilename() == "modules") {
          return $this->getRelativeDkanModulePath($moduleName, $fileInfo->getPathname());
        }
      }
    }
  }

  /**
   * Private.
   */
  private function getRelativeDkanPath() {
    $path = __DIR__;

    while (TRUE) {
      $content = glob($path . "/*");
      $content = array_map(function ($item) use ($path) {
        return str_replace($path, "", $item);
      }, $content);

      if (in_array("/dkan.info.yml", $content)) {
        return $path;
      }

      $path .= "/..";
    }
  }

  /**
   * Private.
   */
  private function getRelativeDrupalPath() {
    return getenv('DRUPAL_ROOT');
  }

  /**
   * Private.
   */
  private function getQueryContainer($data = '', array $info = [], $mockMap = TRUE) {

    $options = (new Options())
      ->add("dkan.metastore.storage", DataFactory::class)
      ->add("dkan.datastore.service", Service::class)
      ->add("dkan.datastore.query", Query::class)
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
      ->add(ConfigFactoryInterface::class, 'get', ImmutableConfig::class)
      ->add(ImmutableConfig::class, 'get', 500);

    if ($mockMap) {
      $chain->add(Query::class, "getQueryStorageMap", ['t' => $this->mockDatastoreTable()]);
    }

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

    $storage = new SqliteDatabaseTable($connection, new DatastoreResource("2", "data.csv", "text/csv"));
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
