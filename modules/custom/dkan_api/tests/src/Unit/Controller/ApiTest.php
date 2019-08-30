<?php

namespace Drupal\Tests\dkan_api\Unit\Controller;

use Drupal\dkan_common\Tests\MockChain;
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

  /**
   *
   */
  public function testGetAll() {
    $mockChain = $this->getCommonMockChain();
    $json = '{"name": "hello"}';
    $mockChain->add(Data::class, 'retrieveAll', json_encode([$json, $json, $json]));

    $controller = Api::create($mockChain->getMock());
    $response = $controller->getAll('dataset');
    $this->assertEquals('[{"name":"hello"},{"name":"hello"},{"name":"hello"}]', $response->getContent());
  }

  /**
   *
   */
  public function testGet() {
    $mockChain = $this->getCommonMockChain();
    $json = '{"name": "hello"}';
    $mockChain->add(Data::class, 'retrieve', json_encode($json));

    $controller = Api::create($mockChain->getMock());
    $response = $controller->get(1, 'dataset');
    $this->assertEquals('{"name":"hello"}', $response->getContent());
  }

  /**
   *
   */
  public function testGetException() {
    $mockChain = $this->getCommonMockChain();;
    $mockChain->add(Data::class, 'retrieve', new \Exception("bad"));

    $controller = Api::create($mockChain->getMock());
    $response = $controller->get(1, 'dataset');
    $this->assertEquals('{"message":"bad"}', $response->getContent());
  }

  /**
   *
   */
  public function testGetExceptionOtherSchema() {
    $mockChain = $this->getCommonMockChain();;
    $mockChain->add(Data::class, 'retrieve', new \Exception("bad"));

    $controller = Api::create($mockChain->getMock());
    $response = $controller->get(1, 'blah');
    $this->assertEquals('{"message":"bad"}', $response->getContent());
  }

  /**
   *
   */
  public function testPost() {
    $mockChain = $this->getCommonMockChain();
    $mockChain->add(RequestStack::class, 'getCurrentRequest', Request::class);
    $mockChain->add(Request::class, 'getRequestUri', "http://blah");
    $mockChain->add(Request::class, 'getContent', json_encode('{"identifier": "1"}'));
    $json = '{"name": "hello"}';
    $mockChain->add(Data::class, 'retrieve', json_encode($json));

    $controller = Api::create($mockChain->getMock());
    $response = $controller->post('dataset');
    $this->assertEquals('{"endpoint":"http:\/\/blah\/1"}', $response->getContent());
  }

  /**
   *
   */
  public function testPostNoIdentifier() {
    $mockChain = $this->getCommonMockChain();
    $mockChain->add(RequestStack::class, 'getCurrentRequest', Request::class);
    $mockChain->add(Request::class, 'getRequestUri', "http://blah");
    $mockChain->add(Request::class, 'getContent', json_encode('{ }'));
    $mockChain->add(Data::class, 'store', "1");

    $controller = Api::create($mockChain->getMock());
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
    $mockChain->add(Request::class, 'getContent', json_encode('{ }'));
    $mockChain->add(Data::class, 'store', new \Exception("bad"));

    $controller = Api::create($mockChain->getMock());
    $response = $controller->post('dataset');
    $this->assertEquals('{"message":"bad"}', $response->getContent());
  }

  /**
   *
   */
  public function testPatch() {
    $mockChain = $this->getCommonMockChain();
    $json = '{"name": "hello"}';
    $mockChain->add(Data::class, 'retrieve', json_encode($json));
    $mockChain->add(Data::class, 'store', "1");
    $mockChain->add(RequestStack::class, 'getCurrentRequest', Request::class);
    $thing = ['identifier' => 1];
    $mockChain->add(Request::class, 'getContent', json_encode(json_encode($thing)));
    $mockChain->add(Request::class, 'getRequestUri', "http://blah");

    $controller = Api::create($mockChain->getMock());
    $response = $controller->patch(1, 'dataset');
    $this->assertEquals('{"endpoint":"http:\/\/blah","identifier":1}', $response->getContent());
  }

  /**
   *
   */
  public function testPatchNoObject() {
    $mockChain = $this->getCommonMockChain();
    $mockChain->add(Data::class, 'retrieve', new \Exception());
    $mockChain->add(Data::class, 'store', "1");
    $mockChain->add(RequestStack::class, 'getCurrentRequest', Request::class);
    $thing = ['identifier' => 1];
    $mockChain->add(Request::class, 'getContent', json_encode(json_encode($thing)));
    $mockChain->add(Request::class, 'getRequestUri', "http://blah");

    $controller = Api::create($mockChain->getMock());
    $response = $controller->patch(1, 'dataset');
    $this->assertEquals('{"message":"No data with the identifier 1 was found."}', $response->getContent());
  }

  /**
   *
   */
  public function testPatchStoreError() {
    $mockChain = $this->getCommonMockChain();
    $mockChain->add(Data::class, 'retrieve', json_encode("{ }"));
    $mockChain->add(Data::class, 'store', new \Exception("Could not store"));
    $mockChain->add(RequestStack::class, 'getCurrentRequest', Request::class);
    $thing = ['identifier' => 1];
    $mockChain->add(Request::class, 'getContent', json_encode(json_encode($thing)));
    $mockChain->add(Request::class, 'getRequestUri', "http://blah");

    $controller = Api::create($mockChain->getMock());
    $response = $controller->patch(1, 'dataset');
    $this->assertEquals('{"message":"Could not store"}', $response->getContent());
  }

  /**
   *
   */
  public function testPatchBadPayload() {
    $mockChain = $this->getCommonMockChain();
    $mockChain->add(Data::class, 'retrieve', json_encode("{ }"));
    $mockChain->add(Data::class, 'store', new \Exception("Could not store"));
    $mockChain->add(RequestStack::class, 'getCurrentRequest', Request::class);
    $mockChain->add(Request::class, 'getContent', json_encode("{"));
    $mockChain->add(Request::class, 'getRequestUri', "http://blah");

    $controller = Api::create($mockChain->getMock());
    $response = $controller->patch(1, 'dataset');
    $this->assertEquals('{"message":"Invalid JSON"}', $response->getContent());
  }

  /**
   *
   */
  public function testPut() {
    $mockChain = $this->getCommonMockChain();
    $mockChain->add(Data::class, 'retrieve', json_encode("{ }"));
    $mockChain->add(Data::class, 'store', "1");
    $mockChain->add(RequestStack::class, 'getCurrentRequest', Request::class);
    $mockChain->add(Request::class, 'getContent', json_encode("{ }"));
    $mockChain->add(Request::class, 'getRequestUri', "http://blah");

    $controller = Api::create($mockChain->getMock());
    $response = $controller->put(1, 'dataset');
    $this->assertEquals('{"endpoint":"http:\/\/blah","identifier":1}', $response->getContent());
  }

  /**
   *
   */
  public function testPutModifyId() {
    $mockChain = $this->getCommonMockChain();
    $mockChain->add(Data::class, 'retrieve', json_encode("{ }"));
    $mockChain->add(Data::class, 'store', "1");
    $mockChain->add(RequestStack::class, 'getCurrentRequest', Request::class);
    $mockChain->add(Request::class, 'getContent', json_encode('{"identifier":"2"}'));
    $mockChain->add(Request::class, 'getRequestUri', "http://blah");

    $controller = Api::create($mockChain->getMock());
    $response = $controller->put(1, 'dataset');
    $this->assertEquals('{"message":"Identifier cannot be modified"}', $response->getContent());
  }

  /**
   *
   */
  public function testPutNoObject() {
    $mockChain = $this->getCommonMockChain();
    $mockChain->add(Data::class, 'retrieve', new \Exception());
    $mockChain->add(Data::class, 'store', "1");
    $mockChain->add(RequestStack::class, 'getCurrentRequest', Request::class);
    $thing = ['identifier' => 1];
    $mockChain->add(Request::class, 'getContent', json_encode(json_encode($thing)));
    $mockChain->add(Request::class, 'getRequestUri', "http://blah");

    $controller = Api::create($mockChain->getMock());
    $response = $controller->put(1, 'dataset');
    $this->assertEquals('{"endpoint":"http:\/\/blah","identifier":1}', $response->getContent());
  }

  /**
   *
   */
  public function testPutStoreError() {
    $mockChain = $this->getCommonMockChain();
    $mockChain->add(Data::class, 'retrieve', new \Exception());
    $mockChain->add(Data::class, 'store', new \Exception("bad"));
    $mockChain->add(RequestStack::class, 'getCurrentRequest', Request::class);
    $thing = ['identifier' => 1];
    $mockChain->add(Request::class, 'getContent', json_encode(json_encode($thing)));
    $mockChain->add(Request::class, 'getRequestUri', "http://blah");

    $controller = Api::create($mockChain->getMock());
    $response = $controller->put(1, 'dataset');
    $this->assertEquals('{"message":"bad"}', $response->getContent());
  }

  /**
   *
   */
  public function testDelete() {
    $mockChain = $this->getCommonMockChain();
    $mockChain->add(Data::class, 'remove', "");

    $controller = Api::create($mockChain->getMock());
    $response = $controller->delete(1, 'dataset');
    $this->assertEquals('{"message":"Dataset 1 has been deleted."}', $response->getContent());
  }

  /**
   *
   */
  private function getCommonMockChain() {
    $mockChain = new MockChain($this);
    $mockChain->add(ContainerInterface::class, 'get',
      [
        'request_stack' => RequestStack::class,
        'dkan_schema.schema_retriever' => SchemaRetriever::class,
        'dkan_data.storage' => Data::class,
      ]
    );
    $mockChain->add(SchemaRetriever::class, 'retrieve', json_encode("{ }"));
    return $mockChain;
  }

}
