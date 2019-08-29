<?php

namespace Drupal\Tests\dkan_api\Unit\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\dkan_api\Controller\Api;
use Drupal\dkan_data\Storage\Data;
use Drupal\dkan_schema\SchemaRetriever;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 *
 */
class ApiTest extends TestCase {

  private $request;

  /**
   *
   */
  public function getContainer() {

    $container = $this->getMockBuilder(ContainerInterface::class)
      ->setMethods(['get'])
      ->disableOriginalConstructor()
      ->getMockForAbstractClass();

    $container->method('get')
      ->with(
        $this->logicalOr(
          $this->equalTo('dkan_schema.schema_retriever'),
          $this->equalTo('dkan_data.storage'),
          $this->equalTo('request_stack')
        )
      )
      ->will($this->returnCallback([$this, 'containerGet']));

    return $container;
  }

  /**
   *
   */
  public function containerGet($input) {
    switch ($input) {
      case 'request_stack':
        $stack = $this->getMockBuilder(RequestStack::class)
          ->disableOriginalConstructor()
          ->setMethods(['getCurrentRequest'])
          ->getMock();

        $stack->method("getCurrentRequest")->willReturn($this->request);

        return $stack;

      break;
      case 'dkan_schema.schema_retriever':
        $schemaRetriever = $this->getMockBuilder(SchemaRetriever::class)
          ->disableOriginalConstructor()
          ->setMethods(['retrieve'])
          ->getMock();

        $schemaRetriever->method('retrieve')->willReturn("{ }");
        return $schemaRetriever;

      break;
      case 'dkan_data.storage':
        $storage = $this->getMockBuilder(Data::class)
          ->disableOriginalConstructor()
          ->setMethods(['retrieveAll', 'retrieve', 'store', 'remove'])
          ->getMock();

        $json = '{"name": "hello"}';
        $storage->method('retrieveAll')->willReturn([$json, $json, $json]);
        $storage->method('retrieve')->willReturn($json);
        $storage->method('store')->willReturn(1);
        $storage->method('remove')->willReturn(1);

        return $storage;

      break;
    }
  }

  /**
   *
   */
  public function testGetAll() {
    $this->request = new Request();
    $controller = Api::create($this->getContainer());
    $response = $controller->getAll('dataset');
    $this->assertEquals('[{"name":"hello"},{"name":"hello"},{"name":"hello"}]', $response->getContent());
  }

  /**
   *
   */
  public function testGet() {
    $this->request = new Request();
    $controller = Api::create($this->getContainer());
    $response = $controller->get(1, 'dataset');
    $this->assertEquals('{"name":"hello"}', $response->getContent());
  }

  /**
   *
   */
  public function testPost() {
    $request = $this->getMockBuilder(Request::class)
      ->setMethods(['getContent', 'getRequestUri'])
      ->disableOriginalConstructor()
      ->getMock();

    $thing = ['identifier' => 1];
    $request->method('getContent')->willReturn(json_encode($thing));
    $request->method('getRequestUri')->willReturn("http://blah");

    $this->request = $request;

    $controller = Api::create($this->getContainer());
    $response = $controller->post('dataset');
    $this->assertEquals('{"endpoint":"http:\/\/blah\/1"}', $response->getContent());
  }

  /**
   *
   */
  public function testPatch() {
    $request = $this->getMockBuilder(Request::class)
      ->setMethods(['getContent', 'getRequestUri'])
      ->disableOriginalConstructor()
      ->getMock();

    $thing = ['identifier' => 1];
    $request->method('getContent')->willReturn(json_encode($thing));
    $request->method('getRequestUri')->willReturn("http://blah");

    $this->request = $request;

    $controller = Api::create($this->getContainer());
    $response = $controller->patch(1, 'dataset');
    $this->assertEquals('{"endpoint":"http:\/\/blah","identifier":1}', $response->getContent());
  }

  /**
   *
   */
  public function testPut() {
    $request = $this->getMockBuilder(Request::class)
      ->setMethods(['getContent', 'getRequestUri'])
      ->disableOriginalConstructor()
      ->getMock();

    $thing = ['identifier' => 1];
    $request->method('getContent')->willReturn(json_encode($thing));
    $request->method('getRequestUri')->willReturn("http://blah");

    $this->request = $request;

    $controller = Api::create($this->getContainer());
    $response = $controller->put(1, 'dataset');
    $this->assertEquals('{"endpoint":"http:\/\/blah","identifier":1}', $response->getContent());
  }

  /**
   *
   */
  public function testDelete() {
    $this->request = new Request();

    $controller = Api::create($this->getContainer());
    $response = $controller->delete(1, 'dataset');
    $this->assertEquals('{"message":"Dataset 1 has been deleted."}', $response->getContent());
  }

}
