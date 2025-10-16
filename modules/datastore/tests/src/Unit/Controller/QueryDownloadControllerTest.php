<?php

namespace Drupal\Tests\datastore\Unit\Controller;

use Drupal\common\DataResource;
use Drupal\Core\Cache\Context\CacheContextsManager;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\common\DatasetInfo;
use Drupal\datastore\Controller\QueryController;
use Drupal\datastore\Controller\QueryDownloadController;
use Drupal\datastore\DatastoreService;
use Drupal\datastore\Service\Query;
use Drupal\datastore\Storage\SqliteDatabaseTable;
use Drupal\metastore\MetastoreApiResponse;
use Drupal\metastore\NodeWrapper\Data;
use Drupal\metastore\NodeWrapper\NodeDataFactory;
use Drupal\metastore\Storage\DataFactory;
use Drupal\sqlite\Driver\Database\sqlite\Connection as SqliteConnection;
use MockChain\Chain;
use MockChain\Options;
use Pdo\Sqlite;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @group dkan
 * @group datastore
 * @group unit
 */
class QueryDownloadControllerTest extends TestCase {

  const FILE_DIR = __DIR__ . "/../../../data/";

  /**
   * Output buffer.
   */
  private string $buffer;

  /**
   * Resources to be used in tests.
   *
   * @var \Drupal\common\DataResource[]
   */
  private array $resources;

  protected function setUp(): void {
    parent::setUp();
    // Set cache services
    $options = (new Options)
      ->add('cache_contexts_manager', CacheContextsManager::class)
      ->add('event_dispatcher', EventDispatcher::class)
      ->index(0);
    $chain = (new Chain($this))
      ->add(ContainerInterface::class, 'get', $options)
      ->add(CacheContextsManager::class, 'assertValidTokens', TRUE);
    \Drupal::setContainer($chain->getMock());

    $this->resources = [
      '2' => new DataResource(self::FILE_DIR . 'states_with_dupes.csv', 'text/csv'),
      '3' => new DataResource(self::FILE_DIR . 'years_colors.csv', 'text/csv'),
      '4' => new DataResource(self::FILE_DIR . 'states_with_dupes_link.csv', 'text/csv'),
    ];

    $this->buffer = '';
  }

  protected function tearDown(): void {
    parent::tearDown();
    $this->buffer = '';
  }

  /**
   * Helper function to compare output of streaming vs normal query controller.
   */
  private function queryResultCompare($data, $resource = NULL) {
    $request = $this->mockRequest($data);
    $qController = QueryController::create($this->getQueryContainer(500));
    $response = $resource ? $qController->queryResource($resource, $request) : $qController->query($request);
    $csv = $response->getContent() ?? '';

    $dController = QueryDownloadController::create($this->getQueryContainer(25));
    ob_start(self::getBuffer(...));
    $streamResponse = $resource ? $dController->queryResource($resource, $request) : $dController->query($request);
    $streamResponse->sendContent();
    $streamedCsv = $this->buffer ?? '';
    ob_get_clean();

    $this->assertEquals(count(explode("\n", (string) $csv)), count(explode("\n", $streamedCsv)));
    $this->assertEquals($csv, $streamedCsv);
  }

  /**
   * Test streaming of a CSV file from database.
   */
  public function testStreamedQueryCsv() {
    $data = [
      "resources" => [
        [
          "id" => $this->resources[2]->getIdentifier(),
          "alias" => "t",
        ],
      ],
      "format" => "csv",
    ];
    // Need 2 json responses which get combined on output.
    $this->queryResultCompare($data);
  }

  /**
   * Test streaming of a CSV file from database.
   */
  public function testStreamedResourceQueryCsv() {
    $data = [
      "format" => "csv",
    ];
    // Need 2 json responses which get combined on output.
    $this->queryResultCompare($data, "2");
  }

  /**
   * Test streaming of a CSV file from database.
   */
  public function testStreamedOtherSortCsv() {
    $data = [
      "resources" => [
        [
          "id" => $this->resources[2]->getIdentifier(),
          "alias" => "t",
        ],
      ],
      "format" => "csv",
      "properties" => ["state", "year"],
      "sorts" => [
        [
          'property' => 'state',
          'order' => 'asc',
        ],
        [
          'property' => 'year',
          'order' => 'desc',
        ],
      ],
    ];

    // Need 2 json responses which get combined on output.
    $this->queryResultCompare($data);
  }

  /**
   * Test streaming of a CSV file from database.
   */
  public function testStreamedJoinCsv() {
    $data = [
      "schema" => TRUE,
      "resources" => [
        [
          "id" => $this->resources[2]->getIdentifier(),
          "alias" => "t",
        ],
        [
          "id" => $this->resources[3]->getIdentifier(),
          "alias" => "j",
        ],
      ],
      "properties" => [
        [
          "resource" => "t",
          "property" => "state",
        ],
        [
          "resource" => "t",
          "property" => "year",
        ],
        [
          "resource" => "j",
          "property" => "color",
        ],
      ],
      "joins" => [
        [
          "resource" => 'j',
          "condition" => [
            'resource' => 't',
            'property' => 'year',
            'value' => [
              'resource' => 'j',
              'property' => 'year',
            ],
          ],
        ],
      ],
      "format" => "csv",
      "sorts" => [
        [
          'resource' => 'j',
          'property' => 'color',
          'order' => 'desc',
        ],
        [
          'property' => 'year',
          'order' => 'asc',
        ],
        [
          'property' => 'state',
          'order' => 'desc',
        ],
      ],
    ];
    $this->queryResultCompare($data);
  }

  /**
   * Test json stream (without specifying csv format; shouldn't work).
   */
  public function testStreamedQueryJson() {
    $data = json_encode([
      "resources" => [
        [
          "id" => $this->resources[2]->getIdentifier(),
          "alias" => "t",
        ],
      ],
    ]);
    // Need 2 json responses which get combined on output.
    $container = $this->getQueryContainer(50);
    $webServiceApi = QueryDownloadController::create($container);
    $request = $this->mockRequest($data);
    $result = $webServiceApi->query($request);
    $this->assertEquals(400, $result->getStatusCode());
  }

  /**
   * Test CSV stream request with a limit higher than the datastore row limit setting.
   */
  public function testStreamedLimit() {
    $queryLimit = 75;
    $pageLimit = 50;
    $responseStreamMaxAge = 3600;
    $data = json_encode([
      "resources" => [
        [
          "id" => $this->resources[2]->getIdentifier(),
          "alias" => "t",
        ],
      ],
      "format" => "csv",
      "limit" => $queryLimit,
    ]);
    // Set the row limit to 50 even though we're requesting 1000.
    $container = $this->getQueryContainer($pageLimit, $responseStreamMaxAge);
    $downloadController = QueryDownloadController::create($container);
    $request = $this->mockRequest($data);
    ob_start(self::getBuffer(...));
    /** @var \Symfony\Component\HttpFoundation\StreamedResponse $streamResponse */
    $streamResponse = $downloadController->query($request);
    $this->assertEquals(200, $streamResponse->getStatusCode());
    $streamResponse->sendContent();
    ob_get_clean();
    $streamedCsv = $this->buffer;
    // Check that the CSV has the full queryLimit number of lines, plus header and final newline.
    $this->assertEquals(($queryLimit + 2), count(explode("\n", $streamedCsv)));
    // Check that the max-age header is correct.
    $this->assertEquals(3600, $streamResponse->getMaxAge());
    $this->assertStringContainsString(
      'public',
      $streamResponse->headers->get('cache-control') ?? ''
    );
  }

  /**
   * Ensure that CSV header correct if columns specified.
   */
  public function testStreamedCsvSpecificColumns() {
    $data = [
      "resources" => [
        [
          "id" => $this->resources[2]->getIdentifier(),
          "alias" => "t",
        ],
      ],
      "format" => "csv",
      "properties" => ["state", "year"],
    ];
    $this->queryResultCompare($data);
  }

  /**
   * Ensure that pagination and CSV header correct if resource-specific columns.
   */
  public function testStreamedCsvResourceColumns() {
    $data = [
      "resources" => [
        [
          "id" => $this->resources[2]->getIdentifier(),
          "alias" => "t",
        ],
      ],
      "format" => "csv",
      "properties" => [
        [
          "resource" => "t",
          "property" => "state",
        ],
        [
          "resource" => "t",
          "property" => "year",
        ],
      ],
    ];

    $this->queryResultCompare($data);
  }


  /**
   * Ensure that rowIds appear correctly if requested.
   */
  public function testStreamedCsvRowIds() {
    $data = [
      "resources" => [
        [
          "id" => $this->resources[2]->getIdentifier(),
          "alias" => "t",
        ],
      ],
      "format" => "csv",
      "rowIds" => TRUE,
    ];

    $this->queryResultCompare($data);
  }

  /**
   * Check that a bad schema will return a CSV with an error message.
   */
  public function testStreamedBadSchema() {
    $data = [
      "resources" => [
        [
          "id" => $this->resources[2]->getIdentifier(),
          "alias" => "tx",
        ],
      ],
      "format" => "csv",
    ];
    $request = $this->mockRequest($data);
    $dController = QueryDownloadController::create($this->getQueryContainer(25));
    ob_start(self::getBuffer(...));
    $streamResponse = $dController->query($request);
    $streamResponse->sendContent();
    $streamedCsv = $this->buffer;
    ob_get_clean();

    $this->assertStringContainsString("Could not generate header", $streamedCsv);
  }

  /**
   * Make sure we get what we expect with invalid JSON.
   */
  public function testInvalidJson() {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Invalid JSON');
    $sampleJson = $this->getBadJson();
    $schema = $this->getSampleSchema();
    $request = $this->mockRequest($sampleJson);
    QueryDownloadController::getPayloadJson($request, $schema);
  }

  /**
   * Create a mock chain for the main container passed to the controller.
   *
   * @param int $rowLimit
   *   The row limit for a query.
   * @param int|null $responseStreamMaxAge
   *   The max age for the response stream in cache, or NULL to use the default.
   *
   * @return \PHPUnit\Framework\MockObject\MockObject
   *   MockChain mock object.
   */
  private function getQueryContainer(int $rowLimit, ?int $responseStreamMaxAge = NULL) {
    $pdo = (class_exists(Sqlite::class)) ? new Sqlite('sqlite::memory:') : new \PDO('sqlite::memory:');
    $connection = new SqliteConnection($pdo, []);
    $options = (new Options())
      ->add("dkan.metastore.storage", DataFactory::class)
      ->add("dkan.datastore.service", DatastoreService::class)
      ->add("dkan.datastore.query", Query::class)
      ->add("dkan.common.dataset_info", DatasetInfo::class)
      ->add('config.factory', ConfigFactoryInterface::class)
      ->add('dkan.metastore.metastore_item_factory', NodeDataFactory::class)
      ->add('dkan.metastore.api_response', MetastoreApiResponse::class)
      ->index(0);

    $schema2 = [
      'record_number' => [
        'type' => 'int',
        'description' => 'Record Number',
        'not null' => TRUE,
      ],
      'state' => [
        'type' => 'text',
        'description' => 'State',
      ],
      'year' => [
        'type' => 'int',
        'description' => 'Year',
      ],
    ];
    $schema3 = [
      'record_number' => [
        'type' => 'int',
        'description' => 'Record Number',
        'not null' => TRUE,
      ],
      'year' => [
        'type' => 'int',
        'description' => 'Year',
      ],
      'color' => [
        'type' => 'text',
        'description' => 'Color',
      ],
    ];

    $storage2 = $this->mockDatastoreTable($this->resources[2], $schema2, $connection);
    $storage2x = $this->mockDatastoreTable($this->resources[4], $schema2, $connection);
    $storage2x->setSchema(['fields' => []]);
    $storage3 = $this->mockDatastoreTable($this->resources[3], $schema3, $connection);
    $storageMap = [
      't' => $storage2,
      'tx' => $storage2x,
      'j' => $storage3,
    ];

    $chain = (new Chain($this))
      ->add(Container::class, "get", $options)
      ->add(DatasetInfo::class, "gather", [])
      ->add(MetastoreApiResponse::class, 'getMetastoreItemFactory', NodeDataFactory::class)
      ->add(MetastoreApiResponse::class, 'addReferenceDependencies', NULL)
      ->add(NodeDataFactory::class, 'getInstance', Data::class)
      ->add(Data::class, 'getCacheContexts', ['url'])
      ->add(Data::class, 'getCacheTags', ['node:1'])
      ->add(Data::class, 'getCacheMaxAge', 0)
      ->add(ConfigFactoryInterface::class, 'get', ImmutableConfig::class)
      ->add(Query::class, "getQueryStorageMap", $storageMap)
      ->add(Query::class, 'getDatastoreService', DatastoreService::class)
      ->add(DatastoreService::class, 'getDataDictionaryFields', NULL)
      // @todo Use an Options or Sequence return here; this will only work for one arg at a time.
      ->add(ImmutableConfig::class, 'get', $rowLimit)
      ->add(ImmutableConfig::class, 'get', $responseStreamMaxAge);

    return $chain->getMock();
  }

  /**
   * We just test POST requests; logic for other methods is tested elsewhere.
   *
   * @param string $data
   *   Request body.
   */
  public function mockRequest($data = '') {
    if (is_array($data) || is_object($data)) {
      $body = json_encode($data);
    }
    else {
      $body = $data;
    }
    return Request::create("http://example.com", 'POST', [], [], [], [], $body);
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
  public function mockDatastoreTable(DataResource $resource, $fields, $connection) {

    $storage = new SqliteDatabaseTable(
      $connection,
      $resource,
      $this->createStub(LoggerInterface::class),
      $this->createStub(EventDispatcherInterface::class)
    );
    $storage->setSchema([
      'fields' => $fields,
    ]);
    $storage->setTable();

    foreach ($fields as $field) {
      $types[] = $field['type'];
    }

    $fp = fopen($resource->getFilePath(), 'rb');
    $sampleData = [];
    while (!feof($fp)) {
      $sampleData[] = fgetcsv($fp, escape: '\\');
    }
    fclose($fp);

    $table_name = $storage->getTableName();
    foreach ($sampleData as $row) {
      $values = [];
      foreach ($row as $key => $value) {
        $values[] = $types[$key] == "int" ? $value : "'$value'";
        $valuesStr = implode(", ", $values);
      }
      $connection->query("INSERT INTO `$table_name` VALUES ($valuesStr);");
    }

    return $storage;
  }

  /**
   * Callback to get output buffer.
   *
   * @param string $buffer
   *   A buffer to be appended to existing buffer in memory.
   */
  protected function getBuffer(string $buffer) {
    $this->buffer .= $buffer;
  }

  private function getBadJson() {
    return file_get_contents(__DIR__ . "/../../../data/query/invalidJson.json");
  }

  private function getSampleSchema() {
    return file_get_contents(__DIR__ . "/../../../data/querySchema.json");
  }

}
