<?php

namespace Drupal\Tests\datastore\Unit\Controller;

use Drupal\datastore\DatastoreResource;
use Drupal\common\DatasetInfo;
use Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Drupal\Core\Cache\Context\CacheContextsManager;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\sqlite\Driver\Database\sqlite\Connection as SqliteConnection;
use Drupal\datastore\Controller\QueryController;
use MockChain\Options;
use Drupal\datastore\DatastoreService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use MockChain\Chain;
use Drupal\datastore\Controller\QueryDownloadController;
use Drupal\datastore\Service\Query;
use Drupal\datastore\Storage\SqliteDatabaseTable;
use Drupal\metastore\MetastoreApiResponse;
use Drupal\metastore\NodeWrapper\Data;
use Drupal\metastore\NodeWrapper\NodeDataFactory;
use Drupal\metastore\Storage\DataFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\common\Storage\SelectFactory;
use Drupal\Core\Database\Query\Select;
use Drupal\Tests\common\Unit\Connection;

/**
 *
 */
class QueryDownloadControllerTest extends TestCase {

  private $buffer;

  protected function setUp(): void {
    parent::setUp();
    // Set cache services
    $options = (new Options)
      ->add('cache_contexts_manager', CacheContextsManager::class)
      ->add('event_dispatcher', ContainerAwareEventDispatcher::class)
      ->index(0);
    $chain = (new Chain($this))
      ->add(ContainerInterface::class, 'get', $options)
      ->add(CacheContextsManager::class, 'assertValidTokens', TRUE);
    \Drupal::setContainer($chain->getMock());
    $this->selectFactory = $this->getSelectFactory();

  }

  protected function tearDown(): void {
    parent::tearDown();
    $this->buffer = NULL;
  }

  /**
   * Helper function to compare output of streaming vs normal query controller.
   */
  private function queryResultCompare($data, $resource = NULL) {
    $request = $this->mockRequest($data);
    $dataDictionaryFields = [
      'name' => 'date',
      'type' => 'date',
      'format '=>'%m/%d/%Y'
    ];
    $qController = QueryController::create($this->getQueryContainer(500));
    $response = $resource ? $qController->queryResource($resource, $request) : $qController->query($request);
    $csv = $response->getContent();

    $dController = QueryDownloadController::create($this->getQueryContainer(25));
    ob_start(['self', 'getBuffer']);
    $streamResponse = $resource ? $dController->queryResource($resource, $request) : $dController->query($request);
    $streamResponse->dataDictionaryFields = $dataDictionaryFields;
    $streamResponse->sendContent();
    $streamedCsv = $this->buffer;
    ob_get_clean();

    $this->assertEquals(count(explode("\n", $csv)), count(explode("\n", $streamedCsv)));
    $this->assertEquals($csv, $streamedCsv);
  }

  /**
   * Test streaming of a CSV file from database.
   */
  public function testStreamedQueryCsv() {
    $data = [
      "resources" => [
        [
          "id" => "2",
          "alias" => "t",
        ],
      ],
      "format" => "csv",
    ];
    // Need 2 json responses which get combined on output.
    $this->queryResultCompare($data);
  }

  public function queryResultReformatted($data){
    $request = $this->mockRequest($data);
    $dataDictionaryFields = [
      'name' => 'date',
      'type' => 'date',
      'format '=>'%m/%d/%Y'
    ];
    $qController = QueryController::create($this->getQueryContainer(500));
    $response = $qController->query($request);
    $csv = $response->getContent();

    $dController = QueryDownloadController::create($this->getQueryContainer(25));
    ob_start(['self', 'getBuffer']);
    $streamResponse = $dController->query($request);
    $streamResponse->dataDictionaryFields = $dataDictionaryFields;
    //$streamResponse->sendContent();
    $this->selectFactory->create($streamResponse);

    $this->assertEquals(count(explode("\n", $csv)), count(explode("\n", $streamedCsv)));
    $this->assertEquals($csv, $streamedCsv);
  }

  /**
   *
   */
  private function getSelectFactory() {
    return new SelectFactory($this->getConnection());
  }

  /**
   *
   */
  private function getConnection() {
    return (new Chain($this))
      ->add(
        Connection::class,
        "select",
        new Select(new Connection(new \PDO('sqlite::memory:'), []), "table", "t")
      )
      ->getMock();
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
          "id" => "2",
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
      "resources" => [
        [
          "id" => "2",
          "alias" => "t",
        ],
        [
          "id" => "3",
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
          "id" => "2",
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
    $data = json_encode([
      "resources" => [
        [
          "id" => "2",
          "alias" => "t",
        ],
      ],
      "format" => "csv",
      "limit" => $queryLimit,
    ]);
    // Set the row limit to 50 even though we're requesting 1000.
    $container = $this->getQueryContainer($pageLimit);
    $downloadController = QueryDownloadController::create($container);
    $request = $this->mockRequest($data);
    ob_start(['self', 'getBuffer']);
    $streamResponse = $downloadController->query($request);
    $this->assertEquals(200, $streamResponse->getStatusCode());
    $streamResponse->sendContent();
    ob_get_clean();
    $streamedCsv = $this->buffer;
    // Check that the CSV has the full queryLimit number of lines, plus header and final newline.
    $this->assertEquals(($queryLimit + 2), count(explode("\n", $streamedCsv)));

  }

  /**
   * Ensure that CSV header correct if columns specified.
   */
  public function testStreamedCsvSpecificColumns() {
    $data = [
      "resources" => [
        [
          "id" => "2",
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
          "id" => "2",
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
          "id" => "2",
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
          "id" => "2",
          "alias" => "tx",
        ],
      ],
      "format" => "csv",
    ];
    $request = $this->mockRequest($data);
    $dController = QueryDownloadController::create($this->getQueryContainer(25));
    ob_start(['self', 'getBuffer']);
    $streamResponse = $dController->query($request);
    $streamResponse->sendContent();
    $streamedCsv = $this->buffer;
    ob_get_clean();

    $this->assertStringContainsString("Could not generate header", $streamedCsv);
  }

  /**
   * Create a mock chain for the main container passed to the controller.
   *
   * @param array $info
   *   Dataset info array mock to be returned by DatasetInfo::gather().
   *
   * @return \MockChain\Chain
   *   MockChain chain object.
   */
  private function getQueryContainer(int $rowLimit) {
    $options = (new Options())
      ->add("dkan.metastore.storage", DataFactory::class)
      ->add("dkan.datastore.service", DatastoreService::class)
      ->add("dkan.datastore.query", Query::class)
      ->add("dkan.common.dataset_info", DatasetInfo::class)
      ->add('config.factory', ConfigFactoryInterface::class)
      ->add('dkan.metastore.metastore_item_factory', NodeDataFactory::class)
      ->add('dkan.metastore.api_response', MetastoreApiResponse::class)
      ->index(0);

    $connection = new SqliteConnection(new \PDO('sqlite::memory:'), []);

    $schema2 = [
      'record_number' => ['type' => 'int', 'not null' => TRUE],
      'state' => ['type' => 'text'],
      'year' => ['type' => 'int'],
    ];
    $schema3 = [
      'record_number' => ['type' => 'int', 'not null' => TRUE],
      'year' => ['type' => 'int'],
      'color' => ['type' => 'text'],
    ];

    $storage2 = $this->mockDatastoreTable($connection, "2", 'states_with_dupes.csv', $schema2);
    $storage2x = clone($storage2);
    $storage2x->setSchema(['fields' => []]);
    $storageMap = [
      't' => $storage2,
      'tx' => $storage2x,
      'j' => $this->mockDatastoreTable($connection, "3", 'years_colors.csv', $schema3
      ),
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
      ->add(Query::class, 'getDatastoreService',  DatastoreService::class)
      ->add(DatastoreService::class, 'getDataDictionaryFields', NULL)
      ->add(ImmutableConfig::class, 'get', $rowLimit);

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
  public function mockDatastoreTable($connection, $id, $csvFile, $fields) {
    foreach ($fields as $name => $field) {
      $types[] = $field['type'];
      $notNull = $field['not null'] ?? FALSE;
      $createFields[] = "`$name` " . strtoupper($field['type']) . (($notNull) ? ' NOT NULL' : '');
    }
    $createFieldsStr = implode(", ", $createFields);
    $connection->query("CREATE TABLE `datastore_$id` ($createFieldsStr);");

    $sampleData = [];
    $fp = fopen(__DIR__ . "/../../../data/$csvFile", 'rb');
    while (!feof($fp)) {
      $sampleData[] = fgetcsv($fp);
    }
    foreach ($sampleData as $row) {
      $values = [];
      foreach ($row as $key => $value) {
        $values[] = $types[$key] == "int" ? $value : "'$value'";
        $valuesStr = implode(", ", $values);
      }
      $connection->query("INSERT INTO `datastore_$id` VALUES ($valuesStr);");
    }

    $storage = new SqliteDatabaseTable($connection, new DatastoreResource($id, "data-$id.csv", "text/csv"));
    $storage->setSchema([
      'fields' => $fields,
    ]);
    return $storage;
  }

  /**
   * Callback to get output buffer.
   *
   * @param $buffer
   */
  protected function getBuffer($buffer) {
    $this->buffer .= $buffer;
  }

}
