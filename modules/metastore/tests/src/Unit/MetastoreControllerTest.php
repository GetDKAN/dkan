<?php

namespace Drupal\Tests\metastore\Unit;

use Drupal\Core\Cache\Context\CacheContextsManager;
use Drupal\metastore\DatasetApiDocs;
use Drupal\metastore\Exception\ExistingObjectException;
use Drupal\metastore\Exception\MissingObjectException;
use Drupal\metastore\Exception\UnmodifiedObjectException;
use Drupal\metastore\ValidMetadataFactory;
use Drupal\metastore\Storage\Data;
use Drupal\metastore\MetastoreService;
use Drupal\metastore\Controller\MetastoreController;
use Drupal\metastore\MetastoreApiResponse;
use Drupal\metastore\NodeWrapper\Data as NodeWrapperData;
use Drupal\metastore\NodeWrapper\NodeDataFactory;
use Drupal\metastore\SchemaRetriever;
use Drupal\metastore\Storage\NodeData;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Query\QueryInterface;

use MockChain\Chain;
use MockChain\Options;
use PHPUnit\Framework\TestCase;
use RootedData\RootedJsonData;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 *
 */
class MetastoreControllerTest extends TestCase {

  /**
   * The ValidMetadataFactory class used for testing.
   *
   * @var \Drupal\metastore\ValidMetadataFactory|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $validMetadataFactory;

  protected function setUp(): void {
    parent::setUp();
    $this->validMetadataFactory = MetastoreServiceTest::getValidMetadataFactory($this);

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
   *
   */
  public function testGetAll() {
    $data = ['name' => 'hello'];
    $dataWithRefs = ["name" => "hello", '%Ref:name' => ["identifier" => "123", "data" => "hello"]];
    $objectWithRefs = $this->validMetadataFactory->get(json_encode($dataWithRefs), 'blah');
    $mockChain = $this->getCommonMockChain();
    $mockChain->add(MetastoreService::class, 'getAll', [$objectWithRefs, $objectWithRefs]);
    $mockChain->add(MetastoreService::class, "getValidMetadataFactory", ValidMetadataFactory::class);

    $controller = MetastoreController::create($mockChain->getMock());
    $response = $controller->getAll('dataset', new Request());
    $this->assertEquals(json_encode([$data, $data]), $response->getContent());
  }

  public function testGetAllRefs() {
    $dataWithRefs = ["name" => "hello", '%Ref:name' => ["identifier" => "123", "data" => "hello"]];
    $dataWithSwappedRefs = ["name" => ["identifier" => "123", "data" => "hello"]];
    $objectWithRefs = $this->validMetadataFactory->get(json_encode($dataWithRefs), 'blah');

    $mockChain = $this->getCommonMockChain();
    $mockChain->add(MetastoreService::class, 'getAll', [$objectWithRefs, $objectWithRefs]);
    $mockChain->add(MetastoreService::class, "getValidMetadataFactory", ValidMetadataFactory::class);

    // Try with show ref ids.
    $mockChain->add(Request::class, 'get', TRUE);
    $controller = MetastoreController::create($mockChain->getMock());
    $response = $controller->getAll('dataset', new Request(['show-reference-ids' => TRUE]));
    $this->assertEquals(
      json_encode([$dataWithSwappedRefs, $dataWithSwappedRefs]),
      $response->getContent()
    );
  }

  /**
   *
   */
  public function testGet() {
    $schema_id = 'dataset';
    $identifier = 1;

    $json = '{"name":"hello"}';
    $jsonWithRefs = '{"name": "hello", "%Ref:name": {"identifier": "123", "data": []}}';
    $mockChain = $this->getCommonMockChain()
      ->add(MetastoreService::class, 'get', new RootedJsonData($jsonWithRefs));

    $controller = MetastoreController::create($mockChain->getMock());
    $response = $controller->get($schema_id, $identifier, new Request());
    $this->assertEquals($json, $response->getContent());

    $entityTypeManagerMock = (new Chain($this))
      ->add(EntityTypeManagerInterface::class, 'getStorage', EntityStorageInterface::class)
      ->add(EntityStorageInterface::class, 'getQuery', QueryInterface::class)
      ->add(QueryInterface::class, 'accessCheck', QueryInterface::class)
      ->add(QueryInterface::class, 'condition', QueryInterface::class)
      ->add(QueryInterface::class, 'execute', NULL)
      ->getMock();
    $nodeDataMock = new NodeData($schema_id, $entityTypeManagerMock);
    $container = $this->getCommonMockChain()
      ->add(MetastoreService::class, 'getStorage', $nodeDataMock)
      ->getMock();
    $controller = MetastoreController::create($container);
    $response = $controller->get($schema_id, $identifier, new Request());
    $json = json_decode($response->getContent());
    $this->assertEquals("Error retrieving metadata: {$schema_id} {$identifier} not found.", $json->message);
  }


  /**
   *
   */
  public function testGetDocs() {
    $json = '{"openapi":"3.0.1"}';
    $spec = json_decode($json, TRUE);
    $mockChain = $this->getCommonMockChain();
    $mockChain->add(DatasetApiDocs::class, 'getDatasetSpecific', $spec);

    $controller = MetastoreController::create($mockChain->getMock());
    $response = $controller->getDocs(1, new Request());
    $this->assertEquals($json, $response->getContent());
  }

  public function testGetWithRefs() {
    $jsonWithRefs = '{"name": "hello", "%Ref:name": {"identifier": "123", "data": []}}';
    $jsonWithSwappedRefs = '{"name":{"identifier":"123","data":[]}}';
    $mockChain = $this->getCommonMockChain();
    $mockChain->add(MetastoreService::class, 'get', new RootedJsonData($jsonWithRefs));

    // Try with show ref ids.
    $controller = MetastoreController::create($mockChain->getMock());
    $response = $controller->get('dataset', '1', new Request(['show-reference-ids' => TRUE]));
    $this->assertEquals($jsonWithSwappedRefs, $response->getContent());
  }

  /**
   *
   */
  public function testGetReferences() {
    $json = '{"name": "hello", "%Ref:name": {"identifier": "123", "data": []}}';
    $mockChain = $this->getCommonMockChain();
    $mockChain->add(MetastoreService::class, 'get', new RootedJsonData($json));

    $controller = MetastoreController::create($mockChain->getMock());
    $response = $controller->get('dataset', 1, new Request(['show-reference-ids' => TRUE]));
    // References should be swapped.
    $this->assertEquals('{"name":{"identifier":"123","data":[]}}', $response->getContent());
  }

  /**
   *
   */
  public function testGetException() {
    $mockChain = $this->getCommonMockChain();
    $mockChain->add(MetastoreService::class, 'get', new \Exception("bad"));

    $controller = MetastoreController::create($mockChain->getMock());
    $response = $controller->get(1, 'dataset', new Request());
    $this->assertStringContainsString('"message":"bad","status":404', $response->getContent());
  }

  /**
   *
   */
  public function testPost() {
    $mockChain = $this->getCommonMockChain();
    $mockChain->add(MetastoreService::class, "getValidMetadataFactory", ValidMetadataFactory::class);
    $mockChain->add(ValidMetadataFactory::class, "get", RootedJsonData::class);
    $mockChain->add(MetastoreService::class, 'post', "1");

    $controller = MetastoreController::create($mockChain->getMock());
    $response = $controller->post('dataset', $this->request('POST', '{"identifier": "1"}'));
    $this->assertEquals('{"endpoint":"\/api\/1","identifier":"1"}', $response->getContent());
  }

  /**
   *
   */
  public function testPostNoIdentifier() {
    $mockChain = $this->getCommonMockChain();
    $mockChain->add(MetastoreService::class, "getValidMetadataFactory", ValidMetadataFactory::class);
    $mockChain->add(ValidMetadataFactory::class, "get", RootedJsonData::class);
    $mockChain->add(MetastoreService::class, 'post', "1");

    $controller = MetastoreController::create($mockChain->getMock());
    $response = $controller->post('dataset', $this->request('POST', '{ }'));
    $this->assertEquals('{"endpoint":"\/api\/1","identifier":"1"}', $response->getContent());
  }

  /**
   *
   */
  public function testPostNoIdentifierException() {
    $mockChain = $this->getCommonMockChain();
    $mockChain->add(MetastoreService::class, "getValidMetadataFactory", ValidMetadataFactory::class);
    $mockChain->add(ValidMetadataFactory::class, "get", RootedJsonData::class);
    $mockChain->add(MetastoreService::class, 'post', new \Exception("bad"));

    $controller = MetastoreController::create($mockChain->getMock());
    $response = $controller->post('dataset', $this->request('POST', '{ }'));
    $this->assertStringContainsString('"message":"bad","status":400', $response->getContent());
  }

  /**
   *
   */
  public function testPostExistingObjectException() {
    $mockChain = $this->getCommonMockChain()
      ->add(MetastoreService::class, "getValidMetadataFactory", ValidMetadataFactory::class)
      ->add(ValidMetadataFactory::class, "get", RootedJsonData::class)
      ->add(MetastoreService::class, 'post', new ExistingObjectException("Already exists"));

    $controller = MetastoreController::create($mockChain->getMock());
    $response = $controller->post('dataset', $this->request('POST', '{"identifier": "1"}'));
    $this->assertStringContainsString('"message":"Already exists","status":409', $response->getContent());
  }

  /**
   *
   */
  public function testPut() {
    $mockChain = $this->getCommonMockChain();
    $mockChain->add(MetastoreService::class, "getValidMetadataFactory", ValidMetadataFactory::class);
    $mockChain->add(ValidMetadataFactory::class, "get", RootedJsonData::class);
    $mockChain->add(MetastoreService::class, "put", ["identifier" => "1", "new" => FALSE]);

    $controller = MetastoreController::create($mockChain->getMock());
    $response = $controller->put('dataset', 1, $this->request('PUT', '{ }'));
    $this->assertEquals('{"endpoint":"\/api","identifier":"1"}', $response->getContent());
  }

  /**
   *
   */
  public function testPatchInvalidJsonException() {
    $mockChain = $this->getCommonMockChain();

    $controller = MetastoreController::create($mockChain->getMock());
    $response = $controller->patch(1, 'dataset', $this->request('PATCH', '{'));
    $this->assertStringContainsString('"message":"Invalid JSON","status":415', $response->getContent());
  }

  /**
   *
   */
  public function testPatchMissingPayloadException() {
    $mockChain = $this->getCommonMockChain();
    $controller = MetastoreController::create($mockChain->getMock());
    $response = $controller->patch(1, 'dataset', $this->request('PATCH', ''));
    $this->assertStringContainsString('"message":"Empty body"', $response->getContent());
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
      ->add(MetastoreService::class, "getValidMetadataFactory", ValidMetadataFactory::class)
      ->add(ValidMetadataFactory::class, "get", RootedJsonData::class)
      ->add(MetastoreService::class, "put", new UnmodifiedObjectException("No changes"));

    $controller = MetastoreController::create($mockChain->getMock());
    $response = $controller->put('dataset', 1, $this->request('PUT', $updating));
    $this->assertStringContainsString('"message":"No changes","status":403', $response->getContent());
  }

  /**
   *
   */
  public function testPutExceptionOtherThanMetastore() {
    $mockChain = $this->getCommonMockChain()
      ->add(ValidMetadataFactory::class, "get", RootedJsonData::class)
      ->add(MetastoreService::class, 'put', new \Exception("Unknown error"));

    $controller = MetastoreController::create($mockChain->getMock());
    $response = $controller->put('dataset', 1, $this->request());
    $this->assertStringContainsString('"message":"Unknown error"', $response->getContent());
  }

  /**
   *
   */
  public function testPatch() {
    $collection = (object) [];

    $mockChain = $this->getCommonMockChain();
    $mockChain->add(MetastoreService::class, "getValidMetadataFactory", ValidMetadataFactory::class);
    $mockChain->add(ValidMetadataFactory::class, "get", RootedJsonData::class);
    $mockChain->add(MetastoreService::class, "patch", "1");

    $controller = MetastoreController::create($mockChain->getMock());
    $response = $controller->patch('dataset', 1, $this->request('PATCH', json_encode($collection)));
    $this->assertEquals('{"endpoint":"\/api","identifier":1}', $response->getContent());
  }

  /**
   *
   */
  public function testPatchModifyId() {
    $collection = ['identifier' => 1];

    $mockChain = $this->getCommonMockChain();
    $mockChain->add(MetastoreService::class, "patch", "1");

    $controller = MetastoreController::create($mockChain->getMock());
    $response = $controller->patch(1, 'dataset', $this->request('PATCH', json_encode($collection)));
    $this->assertStringContainsString('"message":"Identifier cannot be modified"', $response->getContent());
  }

  /**
   *
   */
  public function testPatchBadPayload() {
    $mockChain = $this->getCommonMockChain();
    $mockChain->add(Data::class, 'retrieve', "{ }");
    $mockChain->add(Data::class, 'store', new \Exception("Could not store"));

    $controller = MetastoreController::create($mockChain->getMock());
    $response = $controller->patch(1, 'dataset', $this->request('PATCH', '{'));
    $this->assertStringContainsString('"message":"Invalid JSON","status":415', $response->getContent());
  }

  /**
   *
   */
  public function testPatchObjectNotFound() {
    $mockChain = $this->getCommonMockChain()
      ->add(MetastoreService::class, "getValidMetadataFactory", ValidMetadataFactory::class)
      ->add(ValidMetadataFactory::class, "get", RootedJsonData::class)
      ->add(MetastoreService::class, "patch", new MissingObjectException("Not found"));

    $controller = MetastoreController::create($mockChain->getMock());
    $response = $controller->patch('dataset', 1, $this->request('PATCH', '{"identifier":"1","title":"foo"}'));
    $this->assertStringContainsString('"message":"Not found"', $response->getContent());
  }

  /**
   *
   */
  public function testPatchExceptionOtherThanMetastore() {
    $mockChain = $this->getCommonMockChain()
      ->add(MetastoreService::class, 'patch', new \Exception("Unknown error"));

    $controller = MetastoreController::create($mockChain->getMock());
    $response = $controller->patch('dataset', 1, $this->request('PATCH', '{}'));
    $this->assertStringContainsString('"message":"Unknown error"', $response->getContent());
  }

  /**
   *
   */
  public function testDelete() {
    $mockChain = $this->getCommonMockChain();
    $mockChain->add(MetastoreService::class, 'delete', "1");

    $controller = MetastoreController::create($mockChain->getMock());
    $response = $controller->delete('dataset', "1");
    $this->assertStringContainsString('"message":"Dataset 1 has been deleted."', $response->getContent());
  }

  /**
   *
   */
  public function testDeleteExceptionOtherThanMetastore() {
    $mockChain = $this->getCommonMockChain()
      ->add(MetastoreService::class, 'delete', new \Exception("Unknown error"));

    $controller = MetastoreController::create($mockChain->getMock());
    $response = $controller->delete('dataset', 1);
    $this->assertStringContainsString('"message":"Unknown error"', $response->getContent());
  }

  /**
   *
   */
  public function testPublishExceptionNotFound() {
    $mockChain = $this->getCommonMockChain()
      ->add(MetastoreService::class, 'publish', new MissingObjectException("Not found"));

    $controller = MetastoreController::create($mockChain->getMock());
    $response = $controller->publish('dataset', 1, $this->request());
    $this->assertStringContainsString('"message":"Not found"', $response->getContent());
  }

  /**
   *
   */
  public function testPublishExceptionOtherThanMetastore() {
    $mockChain = $this->getCommonMockChain()
      ->add(MetastoreService::class, 'publish', new \Exception("Unknown error"));

    $controller = MetastoreController::create($mockChain->getMock());
    $response = $controller->publish('dataset', 1, $this->request());
    $this->assertStringContainsString('"message":"Unknown error"', $response->getContent());
  }

  /**
   *
   */
  public function testPublish() {
    $mockChain = $this->getCommonMockChain();
    $mockChain->add(MetastoreService::class, "publish", true);

    $controller = MetastoreController::create($mockChain->getMock());
    $response = $controller->publish('dataset', "1", $this->request('PUT', '{}'));
    $this->assertStringContainsString('"endpoint":"\/api\/publish","identifier":"1"', $response->getContent());
  }

  /**
   *
   */
  public function testGetCatalog() {
    $catalog = (object) ["foo" => "bar"];

    $mockChain = $this->getCommonMockChain();
    $mockChain->add(MetastoreService::class, 'getCatalog', $catalog);

    $controller = MetastoreController::create($mockChain->getMock());
    $response = $controller->getCatalog();
    $this->assertEquals(json_encode($catalog), $response->getContent());
  }

  /**
   * @todo Silly test. Improve it.
   */
  public function testGetSchema() {
    $mockChain = $this->getCommonMockChain();
    $mockChain->add(SchemaRetriever::class, 'getAllIds', ['dataset']);
    $controller = MetastoreController::create($mockChain->getMock());
    $response = $controller->getSchemas();
    $this->assertEquals('["dataset"]', $response->getContent());

    $schemaId = json_decode($response->getContent())[0];
    $schemaResponse = $controller->getSchema($schemaId);
    $this->assertEquals('{"id":"http:\/\/schema"}', $schemaResponse->getContent());
  }

  /**
   *
   */
  public function testGetCatalogException() {
    $mockChain = $this->getCommonMockChain();
    $mockChain->add(MetastoreService::class, 'getCatalog', new \Exception("bad"));

    $controller = MetastoreController::create($mockChain->getMock());
    $response = $controller->getCatalog();
    $this->assertStringContainsString('"message":"bad"', $response->getContent());
  }

  /**
   * Private.
   */
  private function getCommonMockChain() {
    $options = (new Options)
      ->add('dkan.metastore.metastore_item_factory', NodeDataFactory::class)
      ->add('dkan.metastore.service', MetastoreService::class)
      ->add('dkan.metastore.dataset_api_docs', DatasetApiDocs::class)
      ->add('dkan.metastore.api_response', MetastoreApiResponse::class)
      ->index(0);

    $mockChain = (new Chain($this))
      ->add(ContainerInterface::class, 'get', $options)
      ->add(MetastoreService::class, 'getSchemas', ['dataset'])
      ->add(MetastoreService::class, 'getSchema', (object) ["id" => "http://schema"])
      ->add(MetastoreService::class, 'getValidMetadataFactory', ValidMetadataFactory::class)
      ->add(MetastoreService::class, 'isPublished', TRUE)
      ->add(MetastoreApiResponse::class, 'getMetastoreItemFactory', NodeDataFactory::class)
      ->add(MetastoreApiResponse::class, 'addReferenceDependencies', NULL)
      ->add(NodeDataFactory::class, 'getInstance', NodeWrapperData::class)
      ->add(NodeWrapperData::class, 'getCacheContexts', ['url'])
      ->add(NodeWrapperData::class, 'getCacheTags', ['node:1'])
      ->add(NodeWrapperData::class, 'getCacheMaxAge', 0);

    return $mockChain;
  }

  private function request($method = 'GET', $body = '') {
    return Request::create("http://blah/api", $method, [], [], [], [], $body);
  }

}
