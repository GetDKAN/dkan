<?php

namespace Drupal\Tests\datastore\Service;

use Drupal\common\Resource;
use Drupal\Core\DependencyInjection\Container;
use Drupal\common\Storage\JobStoreFactory;
use Drupal\common\Storage\Query;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Tests\datastore\Traits\TestHelperTrait;
use MockChain\Chain;
use MockChain\Options;
use Drupal\datastore\Service;
use Drupal\datastore\Service\Factory\Import;
use Drupal\datastore\Service\ResourceLocalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\datastore\Service\DatastoreQuery;
use Drupal\datastore\Service\Import as ServiceImport;
use Drupal\datastore\Storage\DatabaseTable;
use Drupal\metastore\Storage\Data;
use Drupal\metastore\Storage\DataFactory;
use Drupal\Tests\common\Unit\Storage\QueryDataProvider as QueryData;

/**
 * @coversDefaultClass \Drupal\datastore\WebServiceApi
 * @group dkan
 */
class QueryTest extends TestCase {
  use TestHelperTrait;

  /**
   * @test
   *
   * @dataProvider queryCompareProvider()
   */
  public function testQueryCompare($testName) {
    $datastoreService = Service::create($this->getCommonMockChain());
    $datastoreQuery = $this->getDatastoreQueryFromJson($testName);
    $dkanQuery = $datastoreService->populateQuery($datastoreQuery);
    $dkanQueryCompare = QueryData::$testName(QueryData::QUERY_OBJECT);
    $dkanQueryCompare->showDbColumns = TRUE;
    $this->assertEquals(serialize($dkanQuery), serialize($dkanQueryCompare));
    $result = $datastoreService->runQuery($datastoreQuery);
    $this->assertIsArray($result->results);
    $this->assertIsNumeric($result->count);
    $this->assertIsObject($result->schema);
    $this->assertInstanceOf(DatastoreQuery::class, $result->query);
  }

  /**
   * Test a basic datastore query and response for expected properties.
   */
  public function testResultsQuery() {
    $datastoreService = Service::create($this->getCommonMockChain());
    $datastoreQuery = $this->getDatastoreQueryFromJson("propertiesQuery");
    $response = $datastoreService->runQuery($datastoreQuery);
    $this->assertTrue(is_object($response->results[0]));
    $this->assertTrue(is_numeric($response->count));
    $this->assertTrue(is_array($response->schema->asdf->fields));
    $this->assertInstanceOf(DatastoreQuery::class, $response->query);
    $this->assertIsObject($response->results[0]);
  }

  /**
   * Test no keys behavior (array instead of keyed object).
   */
  public function testNoKeysQuery() {
    $datastoreService = Service::create($this->getCommonMockChain());
    $datastoreQuery = $this->getDatastoreQueryFromJson("propertiesQuery");
    $datastoreQuery->keys = FALSE;
    $response = $datastoreService->runQuery($datastoreQuery);
    $this->assertIsArray($response->results[0]);
  }

  public function testBadCondition() {
    $this->expectExceptionMessage("Invalid condition");
    $datastoreService = Service::create($this->getCommonMockChain());
    $datastoreQuery = $this->getDatastoreQueryFromJson("badConditionQuery");
    $datastoreService->runQuery($datastoreQuery);
  }

  public function testBadQueryProperty() {
    $this->expectExceptionMessage("Bad query property.");
    $datastoreService = Service::create($this->getCommonMockChain());
    $datastoreQuery = $this->getDatastoreQueryFromJson("badPropertyQuery");
    $datastoreService->runQuery($datastoreQuery);
  }

  public function testTooManyResourcesQuery() {
    $this->expectExceptionMessage("Too many resources specified.");
    $datastoreService = Service::create($this->getCommonMockChain());
    $datastoreQuery = $this->getDatastoreQueryFromJson("tooManyResourcesQuery");
    $datastoreService->runQuery($datastoreQuery);
  }

  /**
   * Data provider for query compare tests.
   */
  public function queryCompareProvider() {
    return [
      ["propertiesQuery"],
      ["expressionQuery"],
      ["nestedExpressionQuery"],
      ["nestedConditionGroupQuery"],
      ["sortQuery"],
      ["joinWithPropertiesFromBothQuery"],
    ];
  }

  private function getDatastoreQueryFromJson($payloadName): DatastoreQuery {
    $payload = file_get_contents(__DIR__ . "/../../data/query/$payloadName.json");
    return DatastoreQuery::hydrate($payload);
  }
  
  /**
   * Build our mockChain.
   */
  public function getCommonMockChain() {

    $options = (new Options())
      ->add("datastore.service", Service::class)
      ->add('dkan.datastore.service.resource_localizer', ResourceLocalizer::class)
      ->add("dkan.datastore.service.factory.import", Import::class)
      ->add('queue', QueueFactory::class)
      ->add('request_stack', RequestStack::class)
      ->add('dkan.common.job_store', JobStoreFactory::class)
      ->add('dkan.metastore.storage', DataFactory::class)
      ->index(0);

    $resource = '{"data":{"%Ref:downloadURL":[{"data":{"identifier":"qwerty","version":"uiop"}}]}}';
    $queryResult = [(object) ["expression" => 123]];

    $chain = (new Chain($this))
      ->add(Container::class, "get", $options)
      ->add(RequestStack::class, 'getCurrentRequest', Request::class)
      ->add(DataFactory::class, "getInstance", Data::class)
      ->add(Data::class, "retrieve", $resource)
      ->add(QueueFactory::class, "get", [])
      ->add(ResourceLocalizer::class, "get", Resource::class)
      ->add(Import::class, "getInstance", ServiceImport::class)
      ->add(ServiceImport::class, "getStorage", DatabaseTable::class)
      ->add(DatabaseTable::class, "query", $queryResult)
      ->add(DatabaseTable::class, "getSchema", ["fields" => ["a", "b"]])
      ->add(DatabaseTable::class, "getTableName", "table2");

    $container = $chain->getMock();
    \Drupal::setContainer($container);
    return $container;
  }

}
