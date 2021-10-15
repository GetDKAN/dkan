<?php

namespace Drupal\Tests\datastore\Unit\Controller;

use Drupal\common\DatasetInfo;
use Drupal\Core\Cache\Context\CacheContextsManager;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use MockChain\Options;
use Drupal\datastore\Service;
use MockChain\Sequence;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use MockChain\Chain;
use Drupal\datastore\Controller\QueryDownloadController;
use Drupal\metastore\MetastoreApiResponse;
use Drupal\metastore\NodeWrapper\Data;
use Drupal\metastore\NodeWrapper\NodeDataFactory;
use Drupal\metastore\Storage\DataFactory;
use RootedData\RootedJsonData;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

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
      ->index(0);
    $chain = (new Chain($this))
      ->add(ContainerInterface::class, 'get', $options)
      ->add(CacheContextsManager::class, 'assertValidTokens', TRUE);
    \Drupal::setContainer($chain->getMock());
  }

  /**
   * Test big csv file.
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
    $container = $this->getQueryContainer($data, 'POST', TRUE);
    $webServiceApi = QueryDownloadController::create($container->getMock());
    ob_start(['self', 'getBuffer']);
    $result = $webServiceApi->query(TRUE);
    $result->sendContent();

    $csv = explode("\n", $this->buffer);
    ob_get_clean();
    $this->assertEquals('record_number,data', $csv[0]);
    $this->assertEquals('1,data', $csv[1]);
    $this->assertEquals('50,data', $csv[50]);
    $this->assertEquals('1,data', $csv[501]);
  }

  /**
   * Test json stream (shouldn't work).
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
    $container = $this->getQueryContainer($data, 'POST', TRUE);
    $webServiceApi = QueryDownloadController::create($container->getMock());
    $result = $webServiceApi->query(TRUE);
    $this->assertEquals(400, $result->getStatusCode());
  }

  /**
   * Test streamed resource csv.
   */
  public function testStreamedResourceQueryCsv() {
    $data = json_encode([
      "format" => "csv",
    ]);
    // Need 2 json responses which get combined on output.
    $container = $this->getQueryContainer($data, 'POST', TRUE);
    $webServiceApi = QueryDownloadController::create($container->getMock());
    ob_start(['self', 'getBuffer']);
    $result = $webServiceApi->queryResource("2", TRUE);
    $result->sendContent();

    $csv = explode("\n", $this->buffer);
    ob_get_clean();
    $this->assertEquals('record_number,data', $csv[0]);
    $this->assertEquals('1,data', $csv[1]);
    $this->assertEquals('50,data', $csv[50]);
    $this->assertEquals('1,data', $csv[501]);
  }

  /**
   * Test streamed resource csv through dataset distribution index.
   */
  public function testStreamedResourceQueryCsvDatasetDistIndex() {
    $data = json_encode([
      "format" => "csv",
    ]);
    // Need 2 json responses which get combined on output.
    $info['latest_revision']['distributions'][0]['distribution_uuid'] = '123';

    $container = $this->getQueryContainer($data, 'POST', TRUE, $info);
    $webServiceApi = QueryDownloadController::create($container->getMock());
    ob_start(['self', 'getBuffer']);
    $result = $webServiceApi->queryDatasetResource("2", "0", TRUE);
    $result->sendContent();

    $csv = explode("\n", $this->buffer);
    ob_get_clean();
    $this->assertEquals('record_number,data', $csv[0]);
    $this->assertEquals('1,data', $csv[1]);
    $this->assertEquals('50,data', $csv[50]);
    $this->assertEquals('1,data', $csv[501]);
  }

  public function testStreamedResourceQueryCsvSpecificColumns() {
    $data = json_encode([
      "resources" => [
        [
          "id" => "2",
          "alias" => "t",
        ],
      ],
      "format" => "csv",
      "properties" => ["record_number", "data"]
    ]);

    $response = file_get_contents(__DIR__ . "/../../../data/response_with_specific_header.json");
    $response = new RootedJsonData($response);

    $container = $this->getQueryContainer($data, 'POST', TRUE)
      ->add(Service::class, "runQuery", $response);

    $webServiceApi = QueryDownloadController::create($container->getMock());
    ob_start(['self', 'getBuffer']);
    $result = $webServiceApi->query(TRUE);
    $result->sendContent();

    $csv = explode("\n", $this->buffer);
    ob_get_clean();
    $this->assertEquals('record_number,data', $csv[0]);
  }

  private function getQueryContainer($data = '', string $method = "POST", bool $stream = FALSE, array $info = []) {
    if ($method == "GET") {
      $request = Request::create("http://example.com?$data", $method);
    }
    else {
      $request = Request::create("http://example.com", $method, [], [], [], [], $data);
    }

    $options = (new Options())
      ->add("dkan.metastore.storage", DataFactory::class)
      ->add("dkan.datastore.service", Service::class)
      ->add("request_stack", RequestStack::class)
      ->add("dkan.common.dataset_info", DatasetInfo::class)
      ->add('config.factory', ConfigFactoryInterface::class)
      ->add('dkan.metastore.metastore_item_factory', NodeDataFactory::class)
      ->add('dkan.metastore.api_response', MetastoreApiResponse::class)
      ->index(0);

    $chain = (new Chain($this))
      ->add(Container::class, "get", $options)
      ->add(RequestStack::class, 'getCurrentRequest', $request)
      ->add(DatasetInfo::class, "gather", $info)
      ->add(MetastoreApiResponse::class, 'getMetastoreItemFactory', NodeDataFactory::class)
      ->add(MetastoreApiResponse::class, 'addReferenceDependencies', NULL)
      ->add(NodeDataFactory::class, 'getInstance', Data::class)
      ->add(Data::class, 'getCacheContexts', ['url'])
      ->add(Data::class, 'getCacheTags', ['node:1'])
      ->add(Data::class, 'getCacheMaxAge', 0)
      ->add(ConfigFactoryInterface::class, 'get', ImmutableConfig::class)
      ->add(ImmutableConfig::class, 'get', 500);

    if ($stream) {
      $chain->add(Service::class, "runQuery", $this->addMultipleResponses());
    }
    else {
      $queryResult = new RootedJsonData(file_get_contents(__DIR__ . "/../../../data/response.json"));
      $chain->add(Service::class, 'runQuery', $queryResult);
    }

    return $chain;
  }

  private function addMultipleResponses() {
    $response1 = file_get_contents(__DIR__ . "/../../../data/response_big.json");
    $response1 = new RootedJsonData($response1);

    $response2 = file_get_contents(__DIR__ . "/../../../data/response.json");
    $response2 = new RootedJsonData($response2);

    return (new Sequence())->add($response1)->add($response2);
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
