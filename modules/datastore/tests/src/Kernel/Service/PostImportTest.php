<?php

namespace Drupal\Tests\datastore\Kernel\Service;

use Drupal\common\DataResource;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\datastore\DatastoreService;
use Drupal\datastore\PostImportResult;
use Drupal\datastore\Service\ResourceProcessor\DictionaryEnforcer;
use Drupal\datastore\Service\ResourceProcessor\ResourceDoesNotHaveDictionary;
use Drupal\KernelTests\KernelTestBase;
use Drupal\metastore\DataDictionary\DataDictionaryDiscoveryInterface;
use Drupal\metastore\ResourceMapper;
use Drupal\datastore\Service\PostImport;

/**
 * Tests the PostImport service.
 *
 * @covers \Drupal\datastore\Service\PostImport
 * @coversDefaultClass \Drupal\datastore\Service\PostImport
 *
 * @group dkan
 * @group datastore
 * @group kernel
 */
class PostImportTest extends KernelTestBase {

  protected static $modules = [
    'common',
    'datastore',
    'metastore',
  ];

  /**
   * @covers ::processResource
   */
  public function testProcessResourceChangedResource() {
    $this->installEntitySchema('resource_mapping');

    $this->config('metastore.settings')
    ->set('data_dictionary_mode', DataDictionaryDiscoveryInterface::MODE_SITEWIDE)
    ->save();

    $resource_a = new DataResource('test.csv', 'text/csv');

    $resource_b = (new DataResource('test2.csv', 'text/csv'))->createNewVersion();

    $resource_mapper = $this->getMockBuilder(ResourceMapper::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['get'])
      ->getMock();

    $resource_mapper->expects($this->once())
      ->method('get')
      ->willReturn($resource_a);
    $this->container->set('dkan.metastore.resource_mapper', $resource_mapper);

    // Mock a logger to expect error logging.
    $logger = $this->getMockBuilder(LoggerChannelInterface::class)
      ->onlyMethods(['notice'])
      ->getMockForAbstractClass();
    // Expect one notice.
    $logger->expects($this->once())
      ->method('notice')
      ->with('Cancelling resource processing; resource has changed.');
    $this->container->set('dkan.datastore.logger_channel', $logger);

    $post_import = new PostImport(
      $this->container->get('config.factory'),
      $this->container->get('dkan.datastore.logger_channel'),
      $this->container->get('dkan.datastore.service.resource_processor_collector'),
      $this->container->get('dkan.metastore.data_dictionary_discovery'),
      $this->container->get('dkan.datastore.service'),
      $this->container->get('dkan.datastore.post_import_result_factory'),
    );

    $result = $post_import->processResource($resource_b);

    $this->assertEquals(
      'Cancelling resource processing; resource has changed.',
      $result->getPostImportMessage()
    );
    $this->assertEquals(
      'error',
      $result->getPostImportStatus()
    );
  }

  /**
   * @covers ::processResource
   */
  public function testProcessResourceNonExistentResource() {
    $this->installEntitySchema('resource_mapping');

    $this->config('metastore.settings')
    ->set('data_dictionary_mode', DataDictionaryDiscoveryInterface::MODE_SITEWIDE)
    ->save();

    $resource = new DataResource('test.csv', 'text/csv');
    $resource_mapper = $this->getMockBuilder(ResourceMapper::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['get'])
      ->getMock();
    // Resource returns NULL  
    $resource_mapper->expects($this->once())
      ->method('get')
      ->willReturn(NULL);
    $this->container->set('dkan.metastore.resource_mapper', $resource_mapper);

    // Mock a logger to expect error logging.
    $logger = $this->getMockBuilder(LoggerChannelInterface::class)
      ->onlyMethods(['notice'])
      ->getMockForAbstractClass();
    // Expect one notice.
    $logger->expects($this->once())
      ->method('notice')
      ->with('Cancelling resource processing; resource no longer exists.');
    $this->container->set('dkan.datastore.logger_channel', $logger);

    $post_import = new PostImport(
      $this->container->get('config.factory'),
      $this->container->get('dkan.datastore.logger_channel'),
      $this->container->get('dkan.datastore.service.resource_processor_collector'),
      $this->container->get('dkan.metastore.data_dictionary_discovery'),
      $this->container->get('dkan.datastore.service'),
      $this->container->get('dkan.datastore.post_import_result_factory'),
    );

    $result = $post_import->processResource($resource);

    $this->assertEquals(
      'Cancelling resource processing; resource no longer exists.',
      $result->getPostImportMessage()
    );
    $this->assertEquals(
      'error',
      $result->getPostImportStatus()
    );
  }

  /**
   * @covers ::processResource
   */
  public function testProcessResourceErrorWithFailingDrop() {
    $this->installEntitySchema('resource_mapping');

    $this->config('metastore.settings')
    ->set('data_dictionary_mode', DataDictionaryDiscoveryInterface::MODE_SITEWIDE)
    ->save();

    $this->config('datastore.settings')
      ->set('drop_datastore_on_post_import_error', TRUE)
      ->save();

    $resource = new DataResource('test.csv', 'text/csv');
    $resource_mapper = $this->getMockBuilder(ResourceMapper::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['get'])
      ->getMock();
    $resource_mapper->expects($this->once())
      ->method('get')
      ->willReturn($resource);
    $this->container->set('dkan.metastore.resource_mapper', $resource_mapper);

    // Our error result.
    $error_result = $this->getMockBuilder(PostImportResult::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['getPostImportStatus'])
      ->getMock();
    $error_result->expects($this->any())
      ->method('getPostImportStatus')
      ->willReturn('error');

    // Mock a logger to expect error logging.
    $logger = $this->getMockBuilder(LoggerChannelInterface::class)
      ->onlyMethods(['error'])
      ->getMockForAbstractClass();
    $logger->expects($this->any())
      ->method('error');
    $this->container->set('dkan.datastore.logger_channel', $logger);

    // Datastore service rigged to explode.
    $datastore_service = $this->getMockBuilder(DatastoreService::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['drop', 'getResourceMapper'])
      ->getMock();
    $datastore_service->expects($this->once())
      ->method('drop')
      ->willThrowException(new \Exception('drop error'));
    $datastore_service->expects($this->any())
      ->method('getResourceMapper')
      ->willReturn($resource_mapper);
    $this->container->set('dkan.datastore.service', $datastore_service);

    $post_import = new PostImport(
      $this->container->get('config.factory'),
      $this->container->get('dkan.datastore.logger_channel'),
      $this->container->get('dkan.datastore.service.resource_processor_collector'),
      $this->container->get('dkan.metastore.data_dictionary_discovery'),
      $this->container->get('dkan.datastore.service'),
      $this->container->get('dkan.datastore.post_import_result_factory'),
    );

    $result = $post_import->processResource($resource);

    $this->assertEquals(
      'Attempted to retrieve a sitewide data dictionary, but none was set.',
      $result->getPostImportMessage()
    );
    $this->assertEquals(
      'error',
      $result->getPostImportStatus()
    );
  }

  /**
   * @covers ::processResource
   */
  public function testProcessResourceDropOnPostImportDisabled() {
    $this->installEntitySchema('resource_mapping');

    $this->config('metastore.settings')
    ->set('data_dictionary_mode', DataDictionaryDiscoveryInterface::MODE_SITEWIDE)
    ->save();

    // Do NOT drop on post import error
    $this->config('datastore.settings')
      ->set('drop_datastore_on_post_import_error', FALSE)
      ->save();

    $resource = new DataResource('test.csv', 'text/csv');
    $resource_mapper = $this->getMockBuilder(ResourceMapper::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['get'])
      ->getMock();
    $resource_mapper->expects($this->once())
      ->method('get')
      ->willReturn($resource);
    $this->container->set('dkan.metastore.resource_mapper', $resource_mapper);

    // Mock the dictionary enforcer to throw an exception
    $enforcer = $this->getMockBuilder(DictionaryEnforcer::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['process'])
      ->getMock();
    $enforcer->expects($this->once())
      ->method('process')
      ->willThrowException(new \Exception('our test message'));
    $this->container->set('dkan.datastore.service.resource_processor.dictionary_enforcer', $enforcer);

    // Mock a logger to expect error logging.
    $logger = $this->getMockBuilder(LoggerChannelInterface::class)
      ->onlyMethods(['error'])
      ->getMockForAbstractClass();
    $logger->expects($this->once())
      ->method('error')
      ->with('our test message');
    $this->container->set('dkan.datastore.logger_channel', $logger);

    $post_import = new PostImport(
      $this->container->get('config.factory'),
      $this->container->get('dkan.datastore.logger_channel'),
      $this->container->get('dkan.datastore.service.resource_processor_collector'),
      $this->container->get('dkan.metastore.data_dictionary_discovery'),
      $this->container->get('dkan.datastore.service'),
      $this->container->get('dkan.datastore.post_import_result_factory'),
    );

    $result = $post_import->processResource($resource);

    $this->assertEquals(
      'our test message',
      $result->getPostImportMessage()
    );
    $this->assertEquals(
      'error',
      $result->getPostImportStatus()
    );
  }

  /**
   * @covers ::processResource
   */
  public function testProcessResourceErrorWithSuccessfulDrop() {
    $this->installEntitySchema('resource_mapping');

    $this->config('metastore.settings')
    ->set('data_dictionary_mode', DataDictionaryDiscoveryInterface::MODE_SITEWIDE)
    ->save();

    $this->config('datastore.settings')
      ->set('drop_datastore_on_post_import_error', TRUE)
      ->save();

    $resource = new DataResource('test.csv', 'text/csv');
    $resource_mapper = $this->getMockBuilder(ResourceMapper::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['get'])
      ->getMock();
    $resource_mapper->expects($this->once())
      ->method('get')
      ->willReturn($resource);
    $this->container->set('dkan.metastore.resource_mapper', $resource_mapper);

    // Mock the dictionary enforcer to throw an exception
    $enforcer = $this->getMockBuilder(DictionaryEnforcer::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['process'])
      ->getMock();
    $enforcer->expects($this->once())
      ->method('process')
      ->willThrowException(new \Exception('our test message'));
    $this->container->set('dkan.datastore.service.resource_processor.dictionary_enforcer', $enforcer);

    // Mock a logger to expect error logging.
    $logger = $this->getMockBuilder(LoggerChannelInterface::class)
      ->onlyMethods(['notice'])
      ->getMockForAbstractClass();
    $logger->expects($this->once())
      ->method('notice')
      ->with(
        'Successfully dropped the datastore for resource @identifier due to a post import error. Visit the Datastore Import Status dashboard for details.',
        ['@identifier' => $resource->getIdentifier()],
      );
    $this->container->set('dkan.datastore.logger_channel', $logger);

    // Datastore service rigged to explode.
    $datastore_service = $this->getMockBuilder(DatastoreService::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['drop', 'getResourceMapper'])
      ->getMock();
    $datastore_service->expects($this->once())
      ->method('drop');
    $datastore_service->expects($this->any())
      ->method('getResourceMapper')
      ->willReturn($resource_mapper);
    $this->container->set('dkan.datastore.service', $datastore_service);

    $post_import = new PostImport(
      $this->container->get('config.factory'),
      $this->container->get('dkan.datastore.logger_channel'),
      $this->container->get('dkan.datastore.service.resource_processor_collector'),
      $this->container->get('dkan.metastore.data_dictionary_discovery'),
      $this->container->get('dkan.datastore.service'),
      $this->container->get('dkan.datastore.post_import_result_factory'),
    );

    $result = $post_import->processResource($resource);

    $this->assertEquals(
      'our test message',
      $result->getPostImportMessage()
    );
    $this->assertEquals(
      'error',
      $result->getPostImportStatus()
    );
  }

  /**
   * @covers ::processResource
   */
  public function testProcessResourceDataDictionaryDisabled() {
    $this->config('metastore.settings')
      ->set('data_dictionary_mode', DataDictionaryDiscoveryInterface::MODE_NONE)
      ->save();

    $resource = new DataResource('test.csv', 'text/csv');
    $resource_mapper = $this->getMockBuilder(ResourceMapper::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['get'])
      ->getMock();
    $resource_mapper->expects($this->once())
      ->method('get')
      ->willReturn($resource);
    $this->container->set('dkan.metastore.resource_mapper', $resource_mapper);

    $post_import = new PostImport(
      $this->container->get('config.factory'),
      $this->container->get('dkan.datastore.logger_channel'),
      $this->container->get('dkan.datastore.service.resource_processor_collector'),
      $this->container->get('dkan.metastore.data_dictionary_discovery'),
      $this->container->get('dkan.datastore.service'),
      $this->container->get('dkan.datastore.post_import_result_factory'),
    );

    $result = $post_import->processResource($resource);

    $this->assertEquals(
      'Data-Dictionary Disabled',
      $result->getPostImportMessage()
    );
    $this->assertEquals(
      'N/A',
      $result->getPostImportStatus()
    );
  }

  /**
   * @covers ::processResource
   */
  public function testProcessResourceNoDictionary() {
    // Tell the processor to use reference mode for dictionary enforcement.
    $this->config('metastore.settings')
      ->set('data_dictionary_mode', DataDictionaryDiscoveryInterface::MODE_REFERENCE)
      ->save();

    $resource = new DataResource('test.csv', 'text/csv');
    $resource_mapper = $this->getMockBuilder(ResourceMapper::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['get'])
      ->getMock();
    $resource_mapper->expects($this->once())
      ->method('get')
      ->willReturn($resource);
    $this->container->set('dkan.metastore.resource_mapper', $resource_mapper);

    // Mock the dictionary enforcer to throw an exception so that we can avoid
    // node type dependencies.
    $no_dictionary_exception = new ResourceDoesNotHaveDictionary('test', 123);
    $enforcer = $this->getMockBuilder(DictionaryEnforcer::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['process'])
      ->getMock();
    $enforcer->expects($this->once())
      ->method('process')
      ->willThrowException($no_dictionary_exception);
    $this->container->set('dkan.datastore.service.resource_processor.dictionary_enforcer', $enforcer);

    $post_import = new PostImport(
      $this->container->get('config.factory'),
      $this->container->get('dkan.datastore.logger_channel'),
      $this->container->get('dkan.datastore.service.resource_processor_collector'),
      $this->container->get('dkan.metastore.data_dictionary_discovery'),
      $this->container->get('dkan.datastore.service'),
      $this->container->get('dkan.datastore.post_import_result_factory'),
    );

    $result = $post_import->processResource($resource);

    $this->assertEquals(
      'Resource does not have a data dictionary.',
      $result->getPostImportMessage()
    );
    $this->assertEquals(
      'done',
      $result->getPostImportStatus()
    );
  }

}
