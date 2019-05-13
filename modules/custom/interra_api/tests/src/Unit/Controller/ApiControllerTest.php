<?php

  namespace Drupal\Tests\interra_api\Unit\Controller;

  use Drupal\interra_api\Search;
  use Drupal\interra_api\Controller\ApiController;
  use Drupal\dkan_common\Tests\DkanTestBase;
  use JsonSchemaProvider\Provider;
  use Symfony\Component\HttpFoundation\Request;
  use Symfony\Component\HttpFoundation\JsonResponse;
  use Symfony\Component\HttpFoundation\HeaderBag;
  use Drupal\dkan_common\Service\Factory;
  use Harvest\Storage\Storage;
  use Drupal\interra_api\Service\DatasetModifier;
  use Dkan\Datastore\Manager\IManager;

  /**
   * Tests Drupal\interra_api\Controller\ApiController.
   *
   * @coversDefaultClass Drupal\interra_api\Controller\ApiController
   * @group interra_api
   */
  class ApiControllerTest extends DkanTestBase {

    /**
     * Tests schemas().
     */
    public function testSchemas() {
      // Setup.
      $mock = $this->getMockBuilder(ApiController::class)
        ->setMethods([
        'fetchSchema',
        'response',
        'jsonResponse',
        ])
        ->disableOriginalConstructor()
        ->getMock();

      $mockRequest = $this->createMock(Request::class);
      $mockResponse = $this->createMock(JsonResponse::class);

      $schemaName = 'dataset';
      // Cheating a bit.
      $schema = json_encode((object) [
        'foo' => uniqid('bar'),
      ]);

      // Expect.
      $mock->expects($this->once())
        ->method('fetchSchema')
        ->with($schemaName)
        ->willReturn($schema);

      $mock->expects($this->never())
        ->method('response');

      $mock->expects($this->once())
        ->method('jsonResponse')
        // Contains a reference to an stdclass.
        // is a bit iffy since not *same* obbject.
        ->with($this->isType('array'))
        ->willReturn($mockResponse);

      // Assert.
      $actual = $mock->schemas($mockRequest);
      $this->assertSame($mockResponse, $actual);
    }

    /**
     * Tests schemas() on exception.
     */
    public function testSchemasException() {
      // Setup.
      $mock = $this->getMockBuilder(ApiController::class)
        ->setMethods([
        'fetchSchema',
        'response',
        'jsonResponse',
        ])
        ->disableOriginalConstructor()
        ->getMock();

      $mockRequest = $this->createMock(Request::class);
      $mockResponse = $this->createMock(JsonResponse::class);

      $schemaName = 'dataset';
      $message = 'something went fubar';

      // Expect.
      $mock->expects($this->once())
        ->method('fetchSchema')
        ->with($schemaName)
        ->willThrowException(new \Exception($message));

      $mock->expects($this->never())
        ->method('jsonResponse');

      $mock->expects($this->once())
        ->method('response')
        ->with($message)
        ->willReturn($mockResponse);

      // Assert.
      $actual = $mock->schemas($mockRequest);
      $this->assertSame($mockResponse, $actual);
    }

    /**
     * Tests schema().
     */
    public function testSchema() {
      // Setup.
      $mock = $this->getMockBuilder(ApiController::class)
        ->setMethods([
        'fetchSchema',
        'response',
        'jsonResponse',
        ])
        ->disableOriginalConstructor()
        ->getMock();

      $mockResponse = $this->createMock(JsonResponse::class);

      $schemaName = uniqid('schema');
      // Cheating a bit.
      $schema = json_encode((object) [
        'foo' => uniqid('bar'),
      ]);

      // Expect.
      $mock->expects($this->once())
        ->method('fetchSchema')
        ->with($schemaName)
        ->willReturn($schema);

      $mock->expects($this->never())
        ->method('response');

      $mock->expects($this->once())
        ->method('jsonResponse')
        ->with(json_decode($schema))
        ->willReturn($mockResponse);

      // Assert.
      $actual = $mock->schema($schemaName);
      $this->assertSame($mockResponse, $actual);
    }

    /**
     * Tests schema() on exception.
     */
    public function testSchemaException() {
      // Setup.
      $mock = $this->getMockBuilder(ApiController::class)
        ->setMethods([
        'fetchSchema',
        'response',
        'jsonResponse',
        ])
        ->disableOriginalConstructor()
        ->getMock();

      $mockResponse = $this->createMock(JsonResponse::class);

      $schemaName = uniqid('schema');

      $message = 'something went fubar';

      // Expect.
      $mock->expects($this->once())
        ->method('fetchSchema')
        ->with($schemaName)
        ->willThrowException(new \Exception($message));

      $mock->expects($this->never())
        ->method('jsonResponse');

      $mock->expects($this->once())
        ->method('response')
        ->with($message)
        ->willReturn($mockResponse);

      // Assert.
      $actual = $mock->schema($schemaName);
      $this->assertSame($mockResponse, $actual);
    }

    /**
     * tests response().
     */
    public function testResponse() {
      // Setup.
      $mock = $this->getMockBuilder(ApiController::class)
        ->setMethods(NULL)
        ->disableOriginalConstructor()
        ->getMock();

      $mockFactory = $this->getMockBuilder(Factory::class)
        ->setMethods(['newJsonResponse'])
        ->disableOriginalConstructor()
        ->getMock();

      $this->setActualContainer([
      'dkan.factory' => $mockFactory,
      ]);

      $mockResponse = $this->createMock(JsonResponse::class);

      $mockHeaderBag = $this->getMockBuilder(HeaderBag::class)
        ->setMethods(['set'])
        ->disableOriginalConstructor()
        ->getMock();

      $mockResponse->headers = $mockHeaderBag;

      $resp = uniqid('foo response');

      // Expect.
      $mockFactory->expects($this->once())
        ->method('newJsonResponse')
        ->with($resp)
        ->willReturn($mockResponse);

      $mockHeaderBag->expects($this->exactly(3))
        ->method('set')
        ->withConsecutive(
          ['Access-Control-Allow-Origin', '*'],
          ['Access-Control-Allow-Methods', 'POST, GET, OPTIONS, PATCH, DELETE'],
          ['Access-Control-Allow-Headers', 'Authorization']
      );

      // Assert.
      $actual = $this->invokeProtectedMethod($mock, 'response', $resp);
      $this->assertSame($mockResponse, $actual);
    }

    /**
     * Tests fetchSchema().
     */
    public function testFetchSchema() {
      // Setup.
      $mock = $this->getMockBuilder(ApiController::class)
        ->setMethods(['getSchemaProvider'])
        ->disableOriginalConstructor()
        ->getMock();
      $mockProvider = $this->getMockBuilder(Provider::class)
        ->setMethods(['retrieve'])
        ->disableOriginalConstructor()
        ->getMock();

      $schemaName = uniqid('schema_name');
      $schema = uniqid('the schema itself');

      // Expect.
      $mock->expects($this->once())
        ->method('getSchemaProvider')
        ->willReturn($mockProvider);

      $mockProvider->expects($this->once())
        ->method('retrieve')
        ->with($schemaName)
        ->willReturn($schema);

      // Assert.
      $this->assertEquals($schema, $this->invokeProtectedMethod($mock, 'fetchSchema', $schemaName));
    }

    /**
     *
     */
    public function testSearch() {
      // Setup.
      $mock = $this->getMockBuilder(ApiController::class)
        ->setMethods(['response'])
        ->disableOriginalConstructor()
        ->getMock();

      $mockSearch = $this->getMockBuilder(Search::class)
        ->setMethods(['index'])
        ->disableOriginalConstructor()
        ->getMock();
      $this->setActualContainer([
      'interra_api.search' => $mockSearch,
      ]);

      $mockRequest = $this->createMock(Request::class);
      $mockResponse = $this->createMock(JsonResponse::class);
      $index = [uniqid('result of some kind')];

      // Expect.
      $mockSearch->expects($this->once())
        ->method('index')
        ->willReturn($index);

      $mock->expects($this->once())
        ->method('response')
        ->with($index)
        ->willReturn($mockResponse);

      // Assert.
      $this->assertSame($mockResponse, $mock->search($mockRequest));
    }

    /**
     * Tests doc().
     */
    public function testDoc() {
      // setup
      $mock = $this->getMockBuilder(ApiController::class)
        ->setMethods([
        'response',
        'docDatasetHandler',
        ])
        ->disableOriginalConstructor()
        ->getMock();

      $mockResponse = $this->createMock(JsonResponse::class);
      $docHandlerReturn = ['foo'];
      $collection = 'dataset';
      $doc = 'foo.json';
      // expect

      $mock->expects($this->once())
        ->method('docDatasetHandler')
        ->with($doc)
        ->willReturn($docHandlerReturn);

      $mock->expects($this->once())
        ->method('response')
        ->with($docHandlerReturn)
        ->willReturn($mockResponse);

      // assert
      $actual = $mock->doc($collection, $doc);
      $this->assertSame($mockResponse, $actual);
    }

    /**
     * Tests doc() with invalid conditions.
     */
    public function testDocInvalid() {
      // setup
      $mock = $this->getMockBuilder(ApiController::class)
        ->setMethods(['response', 'docDatasetHandler'])
        ->disableOriginalConstructor()
        ->getMock();

      $mockResponse = $this->createMock(JsonResponse::class);
      $defaultEmpty = [];
      $collection = uniqid('foobar');
      $doc = 'never-used.json';
      // expect

      $mock->expects($this->never())
        ->method('docDatasetHandler');

      $mock->expects($this->once())
        ->method('response')
        ->with($defaultEmpty)
        ->willReturn($mockResponse);

      // assert
      $actual = $mock->doc($collection, $doc);
      $this->assertSame($mockResponse, $actual);
    }

    /**
     * Tests docDatasetHandler().
     */
    public function testDocDatasetHandler() {
      // setup
      $mock = $this->getMockBuilder(ApiController::class)
        ->setMethods([
        'addDatastoreMetadata',
        ])
        ->disableOriginalConstructor()
        ->getMock();

      $mockDrupalNodeDataset = $this->getMockBuilder(Storage::class)
        ->setMethods([
        'retrieve',
        ])
        ->getMockForAbstractClass();

      $mockDatasetModifier = $this->getMockBuilder(DatasetModifier::class)
        ->setMethods([
        'modifyDataset',
        ])
        ->disableOriginalConstructor()
        ->getMock();

      $this->setActualContainer([
      'dkan_api.storage.drupal_node_dataset' => $mockDrupalNodeDataset,
      'interra_api.service.dataset_modifier' => $mockDatasetModifier,
      ]);

      $uuid = uniqid('foobar');
      $doc = $uuid . '.json';

      $decodedData = (object) [
        'foo' => 'bar',
      ];

      $dataset = json_encode($decodedData);

      $expected = (object) [
        'foo' => 'bar-modified',
      ];

      // expect
      $mockDrupalNodeDataset->expects($this->once())
        ->method('retrieve')
        ->with($uuid)
        ->willReturn($dataset);

      $mock->expects($this->once())
        ->method('addDatastoreMetadata')
        // because is passed by value by phpunit
        ->with($this->isInstanceOf(\stdClass::class))
        ->willReturn($decodedData);

      $mockDatasetModifier->expects($this->once())
        ->method('modifyDataset')
        ->with($decodedData)
        ->willReturn($expected);

      // assert
      $actual = $this->invokeProtectedMethod($mock, 'docDatasetHandler', $doc);
      $this->assertSame($expected, $actual);
    }

    public function testAddDatastoreMetadata() {
      // setup
      $mock = $this->getMockBuilder(ApiController::class)
        ->setMethods(['getDatastoreManager'])
        ->disableOriginalConstructor()
        ->getMock();

      $mockDatastoreManager = $this->getMockBuilder(IManager::class)
        ->setMethods([
        'getTableHeaders',
        'numberOfRecordsImported'])
        ->disableOriginalConstructor()
        ->getMockForAbstractClass();

      $dataset = (object) [
        'identifier' => uniqid('foobarr'),
      ];

      $headers = [
      'foo',
      'bar',
      ];

      $rowCount = 42;
      $headerCount = count($headers);

      // expect
      $mock->expects($this->once())
        ->method('getDatastoreManager')
        ->with($dataset->identifier)
        ->willReturn($mockDatastoreManager);

      $mockDatastoreManager->expects($this->once())
        ->method('getTableHeaders')
        ->willReturn($headers);

      $mockDatastoreManager->expects($this->once())
        ->method('numberOfRecordsImported')
        ->willReturn($rowCount);

      // assert
      $this->assertSame(
        $dataset,
        $this->invokeProtectedMethod($mock, 'addDatastoreMetadata', $dataset)
      );

      $this->assertObjectHasAttribute('columns', $dataset);
      $this->assertObjectHasAttribute('datastore_statistics', $dataset);
      $this->assertEquals($headers, $dataset->columns);
      $this->assertEquals(
        [
        'rows' => $rowCount,
        'columns' => $headerCount,
        ],
        $dataset->datastore_statistics
      );
    }

    /**
     * Tests addDatastoreMetadataNoManager().
     */
    public function testAddDatastoreMetadataNoManager() {

      // setup
      $mock = $this->getMockBuilder(ApiController::class)
        ->setMethods(['getDatastoreManager'])
        ->disableOriginalConstructor()
        ->getMock();

      $mockDatastoreManager = null;
      $dataset = (object) [
        'identifier' => uniqid('foobarr'),
      ];

      // expect
      $mock->expects($this->once())
        ->method('getDatastoreManager')
        ->with($dataset->identifier)
        ->willReturn($mockDatastoreManager);

      // assert
      // should be no change.
      $this->assertSame(
        $dataset,
        $this->invokeProtectedMethod($mock, 'addDatastoreMetadata', $dataset)
      );
      $this->assertObjectNotHasAttribute('columns', $dataset);
      $this->assertObjectNotHasAttribute('datastore_statistics', $dataset);
    }

    /**
     * Tests jsonResponse().
     */
    public function testJsonResponse() {
      // Setup.
      $mock = $this->getMockBuilder(ApiController::class)
        ->setMethods(['response'])
        ->disableOriginalConstructor()
        ->getMock();

      $mockResponse = $this->createMock(JsonResponse::class);

      $mockHeaderBag = $this->getMockBuilder(HeaderBag::class)
        ->setMethods(['set'])
        ->disableOriginalConstructor()
        ->getMock();

      $mockResponse->headers = $mockHeaderBag;

      $resp = uniqid('foo response');

      // Expect.
      $mock->expects($this->once())
        ->method('response')
        ->with($resp)
        ->willReturn($mockResponse);

      $mockHeaderBag->expects($this->once())
        ->method('set')
        ->with("Content-Type", "application/schema+json");

      // Assert.
      $actual = $this->invokeProtectedMethod($mock, 'jsonResponse', $resp);
      $this->assertSame($mockResponse, $actual);
    }

  }
