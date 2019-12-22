<?php

namespace Drupal\Tests\dkan_metastore\Unit;

use PHPUnit\Framework\TestCase;
use MockChain\Chain;
use MockChain\Options;
use Drupal\dkan_metastore\Service;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\dkan_metastore\WebServiceApi;
use Drupal\dkan_data\Storage\Data;
use Drupal\dkan_schema\SchemaRetriever;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 *
 */
class WebServiceApiTest extends TestCase {

  /**
   *
   */
  public function testGetAll() {
    $json = '{"name": "hello"}';
    $object = json_decode($json);
    $objects = [$object, $object, $object];
    $mockChain = $this->getCommonMockChain();
    $mockChain->add(Service::class, 'getAll', [$object, $object, $object]);

    $controller = WebServiceApi::create($mockChain->getMock());
    $response = $controller->getAll('dataset');
    $this->assertEquals(json_encode($objects), $response->getContent());
  }

  /**
   *
   */
  public function testGet() {
    $json = '{"name": "hello"}';
    $mockChain = $this->getCommonMockChain();
    $mockChain->add(Service::class, 'get', $json);

    $controller = WebServiceApi::create($mockChain->getMock());
    $response = $controller->get(1, 'dataset');
    $this->assertEquals('{"name":"hello"}', $response->getContent());
  }

  /**
   *
   */
  public function testGetResources() {
    $mockChain = $this->getCommonMockChain();
    $distributions = [(object) ["title" => "Foo"], (object) ["title" => "Bar"]];
    $mockChain->add(Service::class, 'getResources', $distributions);

    $controller = WebServiceApi::create($mockChain->getMock());
    $response = $controller->getResources(1, 'dataset');
    $this->assertEquals(json_encode($distributions), $response->getContent());
  }

  /**
   *
   */
  public function testGetResourcesException() {
    $mockChain = $this->getCommonMockChain();;
    $mockChain->add(Service::class, 'getResources', new \Exception("bad"));

    $controller = WebServiceApi::create($mockChain->getMock());
    $response = $controller->getResources(1, 'dataset');
    $this->assertEquals('{"message":"bad"}', $response->getContent());
  }

  /**
   *
   */
  public function testGetException() {
    $mockChain = $this->getCommonMockChain();;
    $mockChain->add(Service::class, 'get', new \Exception("bad"));

    $controller = WebServiceApi::create($mockChain->getMock());
    $response = $controller->get(1, 'dataset');
    $this->assertEquals('{"message":"bad"}', $response->getContent());
  }

  /**
   *
   */
  public function testPost() {
    $mockChain = $this->getCommonMockChain();
    $mockChain->add(RequestStack::class, 'getCurrentRequest', Request::class);
    $mockChain->add(Request::class, 'getRequestUri', "http://blah");
    $mockChain->add(Request::class, 'getContent', '{"identifier": "1"}');
    $mockChain->add(Service::class, 'post', "1");

    $controller = WebServiceApi::create($mockChain->getMock());
    $response = $controller->post('dataset');
    $this->assertEquals('{"endpoint":"http:\/\/blah\/1","identifier":"1"}', $response->getContent());
  }

  /**
   *
   */
  public function testPostNoIdentifier() {
    $mockChain = $this->getCommonMockChain();
    $mockChain->add(RequestStack::class, 'getCurrentRequest', Request::class);
    $mockChain->add(Request::class, 'getRequestUri', "http://blah");
    $mockChain->add(Request::class, 'getContent', '{ }');
    $mockChain->add(Service::class, 'post', "1");

    $controller = WebServiceApi::create($mockChain->getMock());
    $response = $controller->post('dataset');
    $this->assertEquals('{"endpoint":"http:\/\/blah\/1","identifier":"1"}', $response->getContent());
  }

  /**
   *
   */
  public function testPostNoIdentifierException() {
    $mockChain = $this->getCommonMockChain();
    $mockChain->add(RequestStack::class, 'getCurrentRequest', Request::class);
    $mockChain->add(Request::class, 'getRequestUri', "http://blah");
    $mockChain->add(Request::class, 'getContent', '{ }');
    $mockChain->add(Service::class, 'post', new \Exception("bad"));

    $controller = WebServiceApi::create($mockChain->getMock());
    $response = $controller->post('dataset');
    $this->assertEquals('{"message":"bad"}', $response->getContent());
  }

  /**
   *
   */
  public function testPatch() {
    $thing = (object) [];

    $mockChain = $this->getCommonMockChain();
    $mockChain->add(RequestStack::class, 'getCurrentRequest', Request::class);
    $mockChain->add(Request::class, 'getContent', json_encode($thing));
    $mockChain->add(Request::class, 'getRequestUri', "http://blah");
    $mockChain->add(Service::class, "patch", "1");

    $controller = WebServiceApi::create($mockChain->getMock());
    $response = $controller->patch('dataset', 1);
    $this->assertEquals('{"endpoint":"http:\/\/blah","identifier":1}', $response->getContent());
  }

  /**
   *
   */
  public function testPatchModifyId() {
    $thing = ['identifier' => 1];

    $mockChain = $this->getCommonMockChain();
    $mockChain->add(RequestStack::class, 'getCurrentRequest', Request::class);
    $mockChain->add(Request::class, 'getContent', json_encode($thing));
    $mockChain->add(Request::class, 'getRequestUri', "http://blah");
    $mockChain->add(Service::class, "patch", "1");

    $controller = WebServiceApi::create($mockChain->getMock());
    $response = $controller->patch(1, 'dataset');
    $this->assertEquals('{"message":"Identifier cannot be modified"}', $response->getContent());
  }

  /**
   *
   */
  public function testPatchBadPayload() {
    $mockChain = $this->getCommonMockChain();
    $mockChain->add(Data::class, 'retrieve', "{ }");
    $mockChain->add(Data::class, 'store', new \Exception("Could not store"));
    $mockChain->add(RequestStack::class, 'getCurrentRequest', Request::class);
    $mockChain->add(Request::class, 'getContent', "{");
    $mockChain->add(Request::class, 'getRequestUri', "http://blah");

    $controller = WebServiceApi::create($mockChain->getMock());
    $response = $controller->patch(1, 'dataset');
    $this->assertEquals('{"message":"Invalid JSON"}', $response->getContent());
  }

  /**
   *
   */
  public function testPut() {
    $mockChain = $this->getCommonMockChain();
    $mockChain->add(RequestStack::class, 'getCurrentRequest', Request::class);
    $mockChain->add(Request::class, 'getContent', "{ }");
    $mockChain->add(Request::class, 'getRequestUri', "http://blah");
    $mockChain->add(Service::class, "put", ["identifier" => "1", "new" => FALSE]);

    $controller = WebServiceApi::create($mockChain->getMock());
    $response = $controller->put(1, 'dataset');
    $this->assertEquals('{"endpoint":"http:\/\/blah","identifier":"1"}', $response->getContent());
  }

  /**
   *
   */
  public function testDelete() {
    $mockChain = $this->getCommonMockChain();
    $mockChain->add(Service::class, 'delete', "1");

    $controller = WebServiceApi::create($mockChain->getMock());
    $response = $controller->delete('dataset', "1");
    $this->assertEquals('{"message":"Dataset 1 has been deleted."}', $response->getContent());
  }

  /**
   *
   */
  private function getCommonMockChain() {
    $mockChain = new Chain($this);
    $mockChain->add(ContainerInterface::class, 'get',
      (new Options)->add('request_stack', RequestStack::class)
        ->add("dkan_metastore.service", Service::class)
    );
    $mockChain->add(SchemaRetriever::class, 'retrieve', "{}");
    return $mockChain;
  }

}
