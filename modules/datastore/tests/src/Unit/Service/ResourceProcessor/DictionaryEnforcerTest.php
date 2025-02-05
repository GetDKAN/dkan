<?php

namespace Drupal\Tests\datastore\Unit\Service\ResourceProcessor;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\Config;
use Drupal\Core\DependencyInjection\Container;
use Drupal\Core\StreamWrapper\PublicStream;
use Drupal\Core\StreamWrapper\StreamWrapperManager;
use Drupal\common\DataResource;
use Drupal\datastore\DataDictionary\AlterTableQueryBuilderInterface;
use Drupal\datastore\DataDictionary\AlterTableQueryInterface;
use Drupal\datastore\Plugin\QueueWorker\PostImportResourceProcessor;
use Drupal\datastore\DatastoreService;
use Drupal\datastore\Service\PostImport;
use Drupal\datastore\Service\ResourceProcessorCollector;
use Drupal\datastore\Service\ResourceProcessor\DictionaryEnforcer;
use Drupal\datastore\Storage\DatabaseTable;
use Drupal\datastore\Storage\DatabaseTableFactory;
use Drupal\metastore\DataDictionary\DataDictionaryDiscovery;
use Drupal\metastore\DataDictionary\DataDictionaryDiscoveryInterface;
use Drupal\metastore\MetastoreService;
use Drupal\metastore\Reference\ReferenceLookup;
use Drupal\metastore\ResourceMapper;
use MockChain\Chain;
use MockChain\Options;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RootedData\RootedJsonData;
use Drupal\Core\Database\Connection;
use Drupal\datastore\PostImportResultFactory;
use Drupal\datastore\PostImportResult;

/**
 * Test \Drupal\datastore\Service\ResourceProcessor\DictionaryEnforcer.
 *
 * @coversDefaultClass \Drupal\datastore\Service\ResourceProcessor\DictionaryEnforcer
 *
 * @group dkan
 * @group datastore
 * @group unit
 */
class DictionaryEnforcerTest extends TestCase {

  /**
   * HTTP host protocol and domain for testing download URL.
   *
   * @var string
   */
  protected const HOST = 'http://example.com';

  /**
   * Test process() succeeds.
   */
  public function testProcess() {
    $resource = new DataResource('test.csv', 'text/csv');

    $alter_table_query_builder = (new Chain($this))
      ->add(AlterTableQueryBuilderInterface::class, 'getQuery', AlterTableQueryInterface::class)
      ->add(AlterTableQueryInterface::class, 'execute')
      ->getMock();

    $metastore_service = (new Chain($this))
      ->add(MetastoreService::class, 'get', new RootedJsonData(json_encode(['data' => ['fields' => []]])))
      ->getMock();

    $dictionary_discovery_service = (new Chain($this))
      ->add(DataDictionaryDiscoveryInterface::class, 'dictionaryIdFromResource', 'dictionary-id')
      ->add(DataDictionaryDiscoveryInterface::class, 'getDataDictionaryMode', DataDictionaryDiscoveryInterface::MODE_SITEWIDE)
      ->getMock();

    $database_table_factory = (new Chain($this))
      ->add(DatabaseTableFactory::class, 'getInstance', DatabaseTable::class)
      ->add(DatabaseTable::class, 'getTableName', 'datastore_table')
      ->getMock();

    $dictionary_enforcer = new DictionaryEnforcer(
      $alter_table_query_builder,
      $metastore_service,
      $dictionary_discovery_service,
      $database_table_factory
    );

    $container_chain = $this->getContainerChain($resource->getVersion())
      ->add(AlterTableQueryInterface::class, 'execute')
      ->add(DataDictionaryDiscoveryInterface::class, 'getDataDictionaryMode', DataDictionaryDiscoveryInterface::MODE_SITEWIDE)
      ->add(ResourceProcessorCollector::class, 'getResourceProcessors', [$dictionary_enforcer])
      ->add(PostImportResultFactory::class, 'initializeFromResource', PostImportResult::class);
    \Drupal::setContainer($container_chain->getMock($resource->getVersion()));

    $mocks = $this->getMockDependencies($resource, '', $dictionary_enforcer);

    $post_import = new PostImport(
      ...array_values($mocks),
    );

    $post_import_resource_processor = new PostImportResourceProcessor(
      [], '', ['cron' => ['lease_time' => 10800]], $post_import
    );

    $post_import_resource_processor->processItem($resource);

    $result = $post_import->processResource($resource);

    $this->assertSame("done", $result->getPostImportStatus(), "The postImportStatus is not 'done'");
  }

  /**
   * Test exception thrown in execute() is caught and logged.
   */
  public function testProcessItemExecuteException() {
    $resource = new DataResource('test.csv', 'text/csv');

    $alter_table_query_builder = (new Chain($this))
      ->add(AlterTableQueryBuilderInterface::class, 'setTable', AlterTableQueryBuilderInterface::class)
      ->add(AlterTableQueryBuilderInterface::class, 'addDataDictionary', AlterTableQueryBuilderInterface::class)
      ->add(AlterTableQueryBuilderInterface::class, 'getQuery', AlterTableQueryInterface::class)
      ->add(AlterTableQueryInterface::class, 'execute', new \Exception('Test Error'))
      ->getMock();
    $metastore_service = (new Chain($this))
      ->add(MetastoreService::class, 'get', new RootedJsonData(json_encode(['data' => ['fields' => []]])))
      ->getMock();
    $dictionary_discovery_service = (new Chain($this))
      ->add(DataDictionaryDiscoveryInterface::class, 'dictionaryIdFromResource', 'data-dictionary')
      ->getMock();
    $database_table_factory = (new Chain($this))
      ->add(DatabaseTableFactory::class, 'getInstance', DatabaseTable::class)
      ->add(DatabaseTable::class, 'getTableName', 'datastore_table')
      ->getMock();
    $dictionary_enforcer = new DictionaryEnforcer(
      $alter_table_query_builder,
      $metastore_service,
      $dictionary_discovery_service,
      $database_table_factory
    );

    $container_chain = $this->getContainerChain($resource->getVersion())
      ->add(AlterTableQueryInterface::class, 'execute')
      ->add(DataDictionaryDiscoveryInterface::class, 'getDataDictionaryMode', DataDictionaryDiscoveryInterface::MODE_SITEWIDE)
      ->add(ResourceProcessorCollector::class, 'getResourceProcessors', [$dictionary_enforcer])
      ->add(PostImportResultFactory::class, 'initializeFromResource', PostImportResult::class);
    \Drupal::setContainer($container_chain->getMock($resource->getVersion()));

    $mocks = $this->getMockDependencies($resource, 'error', $dictionary_enforcer);

    $post_import = new PostImport(
      ...array_values($mocks),
    );

    $post_import_resource_processor = new PostImportResourceProcessor(
      [], '', ['cron' => ['lease_time' => 10800]], $post_import
    );

    $post_import_resource_processor->processItem($resource);

    $result = $post_import->processResource($resource);

    $this->assertSame("error", $result->getPostImportStatus(), "The postImportStatus should produce an 'error'");
  }

  /**
   * Test getting data dictionary fields.
   */
  public function testReturnDataDictionaryFields() {
    $resource = new DataResource('test.csv', 'text/csv');

    $alter_table_query_builder = (new Chain($this))
      ->add(AlterTableQueryBuilderInterface::class, 'getQuery', AlterTableQueryInterface::class)
      ->add(AlterTableQueryInterface::class, 'execute')
      ->getMock();
    $metastore_service = (new Chain($this))
      ->add(MetastoreService::class, 'get', new RootedJsonData(json_encode(['data' => ['fields' => []]])))
      ->getMock();
    $dictionary_discovery_service = (new Chain($this))
      ->add(DataDictionaryDiscoveryInterface::class, 'dictionaryIdFromResource', 'dictionary-id')
      ->add(DataDictionaryDiscoveryInterface::class, 'getDataDictionaryMode', DataDictionaryDiscoveryInterface::MODE_SITEWIDE)
      ->add(DataDictionaryDiscoveryInterface::class, 'getSitewideDictionaryId','2')
      ->getMock();
    $database_table_factory = (new Chain($this))
      ->add(DatabaseTableFactory::class, 'getInstance', DatabaseTable::class)
      ->add(DatabaseTable::class, 'getTableName', 'datastore_table')
      ->getMock();

    $dictionary_enforcer = new DictionaryEnforcer(
      $alter_table_query_builder,
      $metastore_service,
      $dictionary_discovery_service,
      $database_table_factory
    );

    $container_chain = $this->getContainerChain($resource->getVersion())
      ->add(AlterTableQueryInterface::class, 'execute')
      ->add(DataDictionaryDiscoveryInterface::class, 'getDataDictionaryMode', DataDictionaryDiscoveryInterface::MODE_SITEWIDE)
      ->add(ResourceProcessorCollector::class, 'getResourceProcessors', [$dictionary_enforcer])
      ->add(DictionaryEnforcer::class, 'returnDataDictionaryFields', ['data' => ['fields' => []]]);
    \Drupal::setContainer($container_chain->getMock($resource->getVersion()));

    $result = $dictionary_enforcer->returnDataDictionaryFields();
    $this->assertIsArray($result);
  }

  /**
   * Setup PostImport container mocks.
   */
  public function getMockDependencies($resource, $expectation, $dictionary_enforcer) {
    $resourceMapperMock = $this->createMock(ResourceMapper::class);
    $resourceMapperMock->expects($this->exactly(2))
      ->method('get')
      ->withAnyParameters()
      ->willReturn($resource);

    $configMock = $this->createMock(Config::class);
    $configFactoryMock = $this->createMock(ConfigFactoryInterface::class);

    $configFactoryMock
      ->method('get')
      ->with('datastore.settings')
      ->willReturn($configMock);

    $configMock
      ->method('get')
      ->with('drop_datastore_on_post_import_error')
      ->willReturn(false);

    $resourceProcessorMock = $this->createMock(ResourceProcessorCollector::class);
    $resourceProcessorMock->expects($this->exactly(2))
      ->method('getResourceProcessors')
      ->willReturn([$dictionary_enforcer]);

    $datastoreServiceMock = $this->createMock(DatastoreService::class);
    $datastoreServiceMock->expects($this->exactly(2))
      ->method('getResourceMapper')
      ->willReturn($resourceMapperMock);

    $queryMock = $this->getMockBuilder('stdClass')
      ->addMethods(['fields', 'execute'])
      ->getMock();

    $queryMock->expects($this->once())
      ->method('fields')
      ->with([
        'resource_identifier' => $resource->getIdentifier(),
        'resource_version' => $resource->getVersion(),
        'post_import_status' => ($expectation === "error") ? 'error' : 'done',
        'post_import_error' => ($expectation === "error") ? 'Test Error' : '',
        'timestamp' => 1700000000,
      ])
      ->willReturnSelf();

    $connectionMock = $this->createMock(Connection::class);
    $connectionMock ->expects($this->once())
      ->method('insert')
      ->with('dkan_post_import_job_status')
      ->willReturn($queryMock);

    $postImportResultFactoryMock = $this->getMockBuilder(PostImportResultFactory::class)
      ->setConstructorArgs([$connectionMock, $resourceMapperMock])
      ->onlyMethods(['getCurrentTime'])
      ->getMock();

    $postImportResultFactoryMock->expects($this->any())
      ->method('getCurrentTime')
      ->willReturn(1700000000);

    return [
      'configFactory' => $configFactoryMock ,
      'logger' => $this->createMock(LoggerInterface::class),
      'resourceProcessorCollector' => $resourceProcessorMock,
      'dataDictionaryDiscovery' => $this->createMock(DataDictionaryDiscoveryInterface::class),
      'datastoreService' => $datastoreServiceMock,
      'postImportResultFactory' => $postImportResultFactoryMock,
    ];
  }

  /**
   * Get container chain.
   */
  protected function getContainerChain(int $resource_version) {

    $options = (new Options())
      ->add('config.factory', ConfigFactoryInterface::class)
      ->add('dkan.datastore.data_dictionary.alter_table_query_builder.mysql', AlterTableQueryBuilderInterface::class)
      ->add('dkan.metastore.data_dictionary_discovery', DataDictionaryDiscovery::class)
      ->add('dkan.datastore.service.post_import', PostImport::class)
      ->add('dkan.datastore.logger_channel', LoggerInterface::class)
      ->add('dkan.metastore.service', MetastoreService::class)
      ->add('dkan.metastore.data_dictionary_discovery', DataDictionaryDiscoveryInterface::class)
      ->add('stream_wrapper_manager', StreamWrapperManager::class)
      ->add('dkan.metastore.resource_mapper', ResourceMapper::class)
      ->add('dkan.datastore.service', DatastoreService::class)
      ->add('dkan.datastore.service.resource_processor_collector', ResourceProcessorCollector::class)
      ->add('dkan.datastore.service.resource_processor.dictionary_enforcer', DictionaryEnforcer::class)
      ->add('dkan.metastore.reference_lookup', ReferenceLookup::class)
      ->add('dkan.datastore.post_import_result_factory', PostImportResultFactory::class)
      ->index(0);

    $json = '{"identifier":"foo","title":"bar","data":{"fields":[]}}';

    return (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(LoggerInterface::class, 'error', NULL, 'error')
      ->add(MetastoreService::class, 'get', new RootedJsonData($json))
      ->add(AlterTableQueryBuilderInterface::class, 'setConnectionTimeout', AlterTableQueryBuilderInterface::class)
      ->add(AlterTableQueryBuilderInterface::class, 'getQuery', AlterTableQueryInterface::class)
      ->add(DataDictionaryDiscoveryInterface::class, 'dictionaryIdFromResource', 'resource_id')
      ->add(DataDictionaryDiscoveryInterface::class, 'getSitewideDictionaryId')
      ->add(DictionaryEnforcer::class, 'returnDataDictionaryFields')
      ->add(PublicStream::class, 'getExternalUrl', self::HOST)
      ->add(StreamWrapperManager::class, 'getViaUri', PublicStream::class)
      ->add(ResourceMapper::class, 'get', DataResource::class)
      ->add(DataResource::class, 'getVersion', $resource_version)
      ->add(ConfigFactoryInterface::class, 'get', FALSE)
      ->add(DatastoreService::class, 'drop');
  }

}
