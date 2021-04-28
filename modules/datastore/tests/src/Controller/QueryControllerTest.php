<?php

use MockChain\Options;
use Drupal\datastore\Service;
use MockChain\Sequence;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use MockChain\Chain;
use Drupal\datastore\Controller\QueryController;
use Drupal\metastore\Storage\DataFactory;
use Ilbee\CSVResponse\CSVResponse as CsvResponse;
use RootedData\RootedJsonData;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 *
 */
class QueryControllerTest extends TestCase {

  private $buffer;

  public function testQueryJson() {
    $data = json_encode([
      "resources" => [
        [
          "id" => "2",
          "alias" => "t",
        ],
      ],
    ]);

    $container = $this->getQueryContainer($data);
    $webServiceApi = QueryController::create($container);
    $result = $webServiceApi->query();

    $this->assertTrue($result instanceof JsonResponse);
    $this->assertEquals(200, $result->getStatusCode());
  }

  public function testQueryInvalid() {
    $data = json_encode([
      "resources" => "nope",
    ]);

    $container = $this->getQueryContainer($data);
    $webServiceApi = QueryController::create($container);
    $result = $webServiceApi->query();

    $this->assertTrue($result instanceof JsonResponse);
    $this->assertEquals(400, $result->getStatusCode());
  }


  public function testResourceQueryInvalidJson() {
    $data = "{[";

    $container = $this->getQueryContainer($data);
    $webServiceApi = QueryController::create($container);
    $result = $webServiceApi->queryResource("2");

    $this->assertTrue($result instanceof JsonResponse);
    $this->assertEquals(400, $result->getStatusCode());
  }

  public function testResourceQueryInvalidQuery() {
    $data = json_encode([
      "conditions" => "nope",
    ]);
    $container = $this->getQueryContainer($data);
    $webServiceApi = QueryController::create($container);
    $result = $webServiceApi->queryResource("2");

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
    $container = $this->getQueryContainer($data);
    $webServiceApi = QueryController::create($container);
    $result = $webServiceApi->queryResource("2");

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

    $container = $this->getQueryContainer($data);
    $webServiceApi = QueryController::create($container);
    $result = $webServiceApi->queryResource("2");

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

    $container = $this->getQueryContainer($data);
    $webServiceApi = QueryController::create($container);
    $result = $webServiceApi->query();

    $this->assertTrue($result instanceof CsvResponse);
    $this->assertEquals(200, $result->getStatusCode());

    $csv = explode("\n", $result->getContent());
    $this->assertEquals('record_number,data', $csv[0]);
    $this->assertEquals('1,data', $csv[1]);
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

    $container = $this->getQueryContainer($data);
    $webServiceApi = QueryController::create($container);
    $result = $webServiceApi->queryResource("2");

    $this->assertTrue($result instanceof CsvResponse);
    $this->assertEquals(200, $result->getStatusCode());
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
    $webServiceApi = QueryController::create($container);
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
    $webServiceApi = QueryController::create($container);
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
    $webServiceApi = QueryController::create($container);
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

  public function testQuerySchema() {
    $container = $this->getQueryContainer();
    $webServiceApi = QueryController::create($container);
    $result = $webServiceApi->querySchema();

    $this->assertTrue($result instanceof JsonResponse);
    $this->assertEquals(200, $result->getStatusCode());
    $this->assertContains("json-schema.org", $result->getContent());
  }

  private function getQueryContainer($data = '', string $method = "POST", bool $stream = FALSE ) {
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
      ->index(0);

    $chain = (new Chain($this))
      ->add(Container::class, "get", $options)
      ->add(RequestStack::class, 'getCurrentRequest', $request);

    if ($stream) {
      $chain->add(Service::class, "runQuery", $this->addMultipleResponses());
    }
    else {
      $queryResult = new RootedJsonData(file_get_contents(__DIR__ . "/../../data/response.json"));
      $chain->add(Service::class, 'runQuery', $queryResult);
    }

    $container = $chain->getMock();
    \Drupal::setContainer($container);
    return $container;
  }

  private function addMultipleResponses() {
    $response1 = file_get_contents(__DIR__ . "/../../data/response_big.json");
    $response1 = new \RootedData\RootedJsonData($response1);

    $response2 = file_get_contents(__DIR__ . "/../../data/response.json");
    $response2 = new \RootedData\RootedJsonData($response2);

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
