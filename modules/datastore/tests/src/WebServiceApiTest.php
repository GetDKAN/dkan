<?php

use Drupal\datastore\CsvResponse;
use MockChain\Options;
use Drupal\datastore\Service;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use MockChain\Chain;
use Drupal\datastore\WebServiceApi;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 *
 */
class WebServiceApiTest extends TestCase {

  /**
   *
   */
  public function testMultipleImports() {
    $container = $this->getContainer();

    $webServiceApi = WebServiceApi::create($container);
    $result = $webServiceApi->import();

    $this->assertTrue($result instanceof JsonResponse);
  }

  /**
   *
   */
  public function testQueryJson() {

    $container = $this->getQueryContainer('json');
    $webServiceApi = WebServiceApi::create($container);
    $result = $webServiceApi->query();

    $this->assertTrue($result instanceof JsonResponse);
    $this->assertEquals(200, $result->getStatusCode());
  }

  /**
   *
   */
  public function testQueryCsv() {

    $container = $this->getQueryContainer('csv');
    $webServiceApi = WebServiceApi::create($container);
    $result = $webServiceApi->query();


    $this->assertTrue($result instanceof CsvResponse);
    $this->assertEquals(200, $result->getStatusCode());

    $csv = explode("\n", $result->getContent());
    $this->assertEquals('record_number,data', $csv[0]);
    $this->assertEquals('1,data', $csv[1]);
  }

  private function getQueryContainer(string $format) {
    $response = file_get_contents(__DIR__ . "/../data/response.json");
    $response = new \RootedData\RootedJsonData($response);
    $paramBag = (new Chain($this))
      ->add(ParameterBag::class, 'get', $format)
      ->getMock();

    $request = (new Chain($this))
      ->add(Request::class, 'getContent', json_encode((object) ["resources" =>
        [ 0 => [
          "alias" => "t",
          "id" => "2"
        ]]]))
      ->getMock();

    $reflection = new ReflectionClass($request);
    $reflection_property = $reflection->getProperty('query');
    $reflection_property->setAccessible(TRUE);
    $reflection_property->setValue($request, $paramBag);

    $options = (new Options())
      ->add("datastore.service", Service::class)
      ->add("request_stack", RequestStack::class)
      ->index(0);

    return (new Chain($this))
      ->add(Container::class, "get", $options)
      ->add(Service::class, "runQuery", $response)
      ->add(RequestStack::class, 'getCurrentRequest', $request)
      ->getMock();
  }

  /**
   * Private.
   */
  private function getContainer() {
    $options = (new Options())
      ->add("datastore.service", Service::class)
      ->add("request_stack", RequestStack::class)
      ->index(0);

    return (new Chain($this))
      ->add(Container::class, "get", $options)
      ->add(Service::class, "drop", NULL)
      ->add(Service::class, "import", [])
      ->add(RequestStack::class, 'getCurrentRequest', Request::class)
      ->add(Request::class, 'getContent', json_encode((object) ['resource_ids' => ["1", "2"]]))
      ->getMock();
  }

}
