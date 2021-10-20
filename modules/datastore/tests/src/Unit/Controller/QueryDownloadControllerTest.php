<?php

namespace Drupal\Tests\datastore\Unit\Controller;

use Dkan\Datastore\Resource;
use Drupal\common\DatasetInfo;
use Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Drupal\Core\Cache\Context\CacheContextsManager;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Database\DatabaseExceptionWrapper;
use Drupal\Core\Database\Driver\sqlite\Connection as SqliteConnection;
use Drupal\Core\Database\IntegrityConstraintViolationException;
use MockChain\Options;
use Drupal\datastore\Service;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use MockChain\Chain;
use Drupal\datastore\Controller\QueryDownloadController;
use Drupal\datastore\Storage\SqliteDatabaseTable;
use Drupal\metastore\MetastoreApiResponse;
use Drupal\metastore\NodeWrapper\Data;
use Drupal\metastore\NodeWrapper\NodeDataFactory;
use Drupal\metastore\Storage\DataFactory;
use Exception as GlobalException;
use InvalidArgumentException;
use ReflectionException;
use PHPUnit\Framework\MockObject\RuntimeException;
use PHPUnit\Framework\Exception;
use LogicException;
use PHPUnit\Framework\ExpectationFailedException;
use RootedData\RootedJsonData;
use SebastianBergmann\RecursionContext\InvalidArgumentException as RecursionContextInvalidArgumentException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\Request;

/**
 *
 */
class QueryDownloadControllerTest extends TestCase {

  private $buffer;

  protected function setUp() {
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
  }

  /**
   * Test streaming of a CSV file from database.
   */
  public function testStreamedQueryCsv() {
    $data = json_encode([
      "resources" => [
        [
          "id" => "2",
          "alias" => "t",
        ],
      ],
      "format" => "csv",
    ]);
    // Need 2 json responses which get combined on output.
    $container = $this->getQueryContainer()->getMock();
    $webServiceApi = QueryDownloadController::create($container);
    $request = $this->mockRequest($data);
    ob_start(['self', 'getBuffer']);
    $result = $webServiceApi->query($request);
    $result->sendContent();

    $csv = explode("\n", trim($this->buffer));
    ob_get_clean();
    // Basic integrity checks.
    $this->assertEquals('state,year', $csv[0]);
    $this->assertEquals('Alabama,2010', $csv[1]);
    $this->assertEquals('Wyoming,2010', $csv[50]);
    $this->assertEquals('Arkansas,2014', $csv[105]);
    $this->assertEquals(count($csv), 106);
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
    $container = $this->getQueryContainer()->getMock();
    $webServiceApi = QueryDownloadController::create($container);
    $request = $this->mockRequest($data);
    $result = $webServiceApi->query($request);
    $this->assertEquals(400, $result->getStatusCode());
  }

  /**
   * Ensure that CSV header correct if columns specified.
   */
  public function testStreamedCsvSpecificColumns() {
    $data = json_encode([
      "resources" => [
        [
          "id" => "2",
          "alias" => "t",
        ],
      ],
      "format" => "csv",
      "properties" => ["record_number", "state"],
    ]);

    $container = $this->getQueryContainer()->getMock();
    $webServiceApi = QueryDownloadController::create($container);
    $request = $this->mockRequest($data);
    ob_start(['self', 'getBuffer']);
    $result = $webServiceApi->query($request);
    $result->sendContent();

    $csv = explode("\n", $this->buffer);
    ob_get_clean();
    $this->assertEquals('record_number,state', $csv[0]);
  }


  /**
   * Ensure that rowIds appear correctly if requested.
   */
  public function testStreamedCsvRowIds() {
    $data = json_encode([
      "resources" => [
        [
          "id" => "2",
          "alias" => "t",
        ],
      ],
      "format" => "csv",
      "rowIds" => true,
    ]);

    $container = $this->getQueryContainer()->getMock();
    $webServiceApi = QueryDownloadController::create($container);
    $request = $this->mockRequest($data);
    ob_start(['self', 'getBuffer']);
    $result = $webServiceApi->query($request);
    $result->sendContent();

    $csv = explode("\n", $this->buffer);
    ob_get_clean();
    $this->assertEquals('record_number,state,year', $csv[0]);
    $this->assertEquals('112,Wyoming,2010', $csv[50]);
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
  private function getQueryContainer(array $info = []) {
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
      ->add(ConfigFactoryInterface::class, 'get', ImmutableConfig::class)
      ->add(Service::class, "getQueryStorageMap", ['t' => $this->mockDatastoreTable()])
      ->add(SqliteConnection::class, "getSchema", [])
      ->add(ImmutableConfig::class, 'get', 50);

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

  /**
   * Callback to get output buffer.
   *
   * @param $buffer
   */
  protected function getBuffer($buffer) {
    $this->buffer .= $buffer;
  }

}
