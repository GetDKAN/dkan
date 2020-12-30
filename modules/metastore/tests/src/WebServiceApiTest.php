<?php

namespace Drupal\Tests\metastore;

use Drupal\metastore\Exception\ExistingObjectException;
use Drupal\metastore\Exception\MissingObjectException;
use Drupal\metastore\Exception\UnmodifiedObjectException;
use Drupal\metastore\Storage\Data;
use Drupal\metastore\Service;
use Drupal\metastore\WebServiceApi;
use Drupal\metastore\SchemaRetriever;
use MockChain\Chain;
use MockChain\Options;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

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
    $mockChain = $this->getCommonMockChain();
    $mockChain->add(Service::class, 'getResources', new \Exception("bad"));

    $controller = WebServiceApi::create($mockChain->getMock());
    $response = $controller->getResources(1, 'dataset');
    $this->assertEquals('{"message":"bad"}', $response->getContent());
  }

  /**
   *
   */
  public function testGetException() {
    $mockChain = $this->getCommonMockChain();
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
  public function testPostExistingObjectException() {
    $mockChain = $this->getCommonMockChain()
      ->add(RequestStack::class, 'getCurrentRequest', Request::class)
      ->add(Request::class, 'getRequestUri', "http://blah")
      ->add(Request::class, 'getContent', '{"identifier": "1"}')
      ->add(Service::class, 'post', new ExistingObjectException("Already exists"));

    $controller = WebServiceApi::create($mockChain->getMock());
    $response = $controller->post('dataset');
    $this->assertEquals('{"message":"Already exists"}', $response->getContent());
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
  public function testPutInvalidJsonException() {
    $mockChain = $this->getCommonMockChain()
      ->add(RequestStack::class, 'getCurrentRequest', Request::class)
      ->add(Request::class, 'getContent', "{");

    $controller = WebServiceApi::create($mockChain->getMock());
    $response = $controller->put(1, 'dataset');
    $this->assertEquals('{"message":"Invalid JSON"}', $response->getContent());
  }

  /**
   *
   */
  public function testPutMissingPayloadException() {
    $mockChain = $this->getCommonMockChain()
      ->add(RequestStack::class, 'getCurrentRequest', Request::class)
      ->add(Request::class, 'getContent', "");

    $controller = WebServiceApi::create($mockChain->getMock());
    $response = $controller->put(1, 'dataset');
    $this->assertEquals('{"message":"Empty body"}', $response->getContent());
  }

  /**
   *
   */
  public function testPutWithEquivalentData() {
    $existing = '{"identifier":"1","title":"Foo"}';
    $updating = <<<EOF
      {
        "title": "Foo",
        "identifier": "1"
      }
EOF;

    $mockChain = $this->getCommonMockChain()
      ->add(RequestStack::class, 'getCurrentRequest', Request::class)
      ->add(Request::class, 'getContent', $updating)
      ->add(Request::class, 'getRequestUri', "http://blah")
      ->add(Service::class, "put", new UnmodifiedObjectException("No changes"));

    $controller = WebServiceApi::create($mockChain->getMock());
    $response = $controller->put('dataset', 1);
    $this->assertEquals('{"message":"No changes"}', $response->getContent());
  }

  /**
   *
   */
  public function testPutExceptionOtherThanMetastore() {
    $mockChain = $this->getCommonMockChain()
      ->add(RequestStack::class, 'getCurrentRequest', new \Exception("Unknown error"));

    $controller = WebServiceApi::create($mockChain->getMock());
    $response = $controller->put('dataset', 1);
    $this->assertEquals('{"message":"Unknown error"}', $response->getContent());
  }

  /**
   *
   */
  public function testPatch() {
    $collection = (object) [];

    $mockChain = $this->getCommonMockChain();
    $mockChain->add(RequestStack::class, 'getCurrentRequest', Request::class);
    $mockChain->add(Request::class, 'getContent', json_encode($collection));
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
    $collection = ['identifier' => 1];

    $mockChain = $this->getCommonMockChain();
    $mockChain->add(RequestStack::class, 'getCurrentRequest', Request::class);
    $mockChain->add(Request::class, 'getContent', json_encode($collection));
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
  public function testPatchObjectNotFound() {
    $mockChain = $this->getCommonMockChain()
      ->add(RequestStack::class, 'getCurrentRequest', Request::class)
      ->add(Request::class, 'getContent', '{"identifier":"1","title":"foo"}')
      ->add(Service::class, "patch", new MissingObjectException("Not found"));

    $controller = WebServiceApi::create($mockChain->getMock());
    $response = $controller->patch('dataset', 1);
    $this->assertEquals('{"message":"Not found"}', $response->getContent());
  }

  /**
   *
   */
  public function testPatchExceptionOtherThanMetastore() {
    $mockChain = $this->getCommonMockChain()
      ->add(RequestStack::class, 'getCurrentRequest', new \Exception("Unknown error"));

    $controller = WebServiceApi::create($mockChain->getMock());
    $response = $controller->patch('dataset', 1);
    $this->assertEquals('{"message":"Unknown error"}', $response->getContent());
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
  public function testDeleteExceptionOtherThanMetastore() {
    $mockChain = $this->getCommonMockChain()
      ->add(Service::class, 'delete', new \Exception("Unknown error"));

    $controller = WebServiceApi::create($mockChain->getMock());
    $response = $controller->delete('dataset', 1);
    $this->assertEquals('{"message":"Unknown error"}', $response->getContent());
  }

  /**
   *
   */
  public function testPublishExceptionNotFound() {
    $mockChain = $this->getCommonMockChain()
      ->add(Service::class, 'publish', new MissingObjectException("Not found"));

    $controller = WebServiceApi::create($mockChain->getMock());
    $response = $controller->publish('dataset', 1);
    $this->assertEquals('{"message":"Not found"}', $response->getContent());
  }

  /**
   *
   */
  public function testPublishExceptionOtherThanMetastore() {
    $mockChain = $this->getCommonMockChain()
      ->add(Service::class, 'publish', new \Exception("Unknown error"));

    $controller = WebServiceApi::create($mockChain->getMock());
    $response = $controller->publish('dataset', 1);
    $this->assertEquals('{"message":"Unknown error"}', $response->getContent());
  }

  /**
   *
   */
  public function testPublish() {
    $mockChain = $this->getCommonMockChain();
    $mockChain->add(RequestStack::class, 'getCurrentRequest', Request::class);
    $mockChain->add(Request::class, 'getContent', '{}');
    $mockChain->add(Request::class, 'getRequestUri', "http://blah");
    $mockChain->add(Service::class, "publish", "1");

    $controller = WebServiceApi::create($mockChain->getMock());
    $response = $controller->publish('dataset', "1");
    $this->assertEquals('{"endpoint":"http:\/\/blah","identifier":"1"}', $response->getContent());
  }

  /**
   *
   */
  public function testGetCatalog() {
    $catalog = (object) ["foo" => "bar"];

    $mockChain = $this->getCommonMockChain();
    $mockChain->add(Service::class, 'getCatalog', $catalog);

    $controller = WebServiceApi::create($mockChain->getMock());
    $response = $controller->getCatalog();
    $this->assertEquals(json_encode($catalog), $response->getContent());
  }

  /**
   *
   */
  public function testGetCatalogException() {
    $mockChain = $this->getCommonMockChain();
    $mockChain->add(Service::class, 'getCatalog', new \Exception("bad"));

    $controller = WebServiceApi::create($mockChain->getMock());
    $response = $controller->getCatalog();
    $this->assertEquals('{"message":"bad"}', $response->getContent());
  }

  /**
   * Private.
   */
  private function getCommonMockChain() {
    $options = (new Options)
      ->add('request_stack', RequestStack::class)
      ->add('dkan.metastore.service', Service::class)
      ->index(0);

    $mockChain = (new Chain($this))
      ->add(ContainerInterface::class, 'get', $options)
      ->add(SchemaRetriever::class, 'retrieve', "{}")
      ->add(RequestStack::class, 'getCurrentRequest', Request::class)
      ->add(Request::class, 'get', FALSE);

    return $mockChain;
  }

}
