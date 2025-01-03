<?php

declare(strict_types=1);

namespace Drupal\Tests\datastore\Kernel\Plugin\QueueWorker;

use Drupal\common\DataResource;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\datastore\DatastoreService;
use Drupal\datastore\Plugin\QueueWorker\PostImportResourceProcessor;
use Drupal\datastore\PostImportResult;
use Drupal\datastore\Service\ResourceProcessor\DictionaryEnforcer;
use Drupal\datastore\Service\ResourceProcessor\ResourceDoesNotHaveDictionary;
use Drupal\KernelTests\KernelTestBase;
use Drupal\metastore\DataDictionary\DataDictionaryDiscoveryInterface;
use Drupal\metastore\ResourceMapper;

/**
 * Test \Drupal\datastore\Plugin\QueueWorker\PostImportResourceProcessor.
 *
 * @coversDefaultClass \Drupal\datastore\Plugin\QueueWorker\PostImportResourceProcessor
 * @covers \Drupal\datastore\Plugin\QueueWorker\PostImportResourceProcessor
 *
 * @group dkan
 * @group datastore
 * @group kernel
 */
class PostImportResourceProcessorTest extends KernelTestBase {

  protected static $modules = [
    'common',
    'datastore',
    'metastore',
  ];

  protected $strictConfigSchema = FALSE;

  /**
   * @covers ::postImportProcessItem
   */
  public function testPostImportProcessItemNoDictionary() {
    // Tell the processor to use reference mode for dictionary enforcement.
    $this->config('metastore.settings')
      ->set('data_dictionary_mode', DataDictionaryDiscoveryInterface::MODE_REFERENCE)
      ->save();

    // Mock the resource mapper to return a given data resource with no
    // describedBy property.
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
    // node type dependenies.
    $no_dictionary_exception = new ResourceDoesNotHaveDictionary('test', 123);
    $enforcer = $this->getMockBuilder(DictionaryEnforcer::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['process'])
      ->getMock();
    $enforcer->expects($this->once())
      ->method('process')
      ->willThrowException($no_dictionary_exception);
    $this->container->set('dkan.datastore.service.resource_processor.dictionary_enforcer', $enforcer);

    // Create a post import processor.
    /** @var \Drupal\datastore\Plugin\QueueWorker\PostImportResourceProcessor $processor */
    $processor = PostImportResourceProcessor::create(
      $this->container,
      [],
      'post_import',
      [
        'cron' => [
          'time' => 180,
          'lease_time' => 10800,
        ],
      ]
    );

    // The results of post import processing should reflect that the resource
    // does not have a data dictionary.
    $result = $processor->postImportProcessItem($resource);
    $this->assertEquals(
      'Resource test does not have a data dictionary.',
      $result->getPostImportMessage()
    );
    $this->assertEquals(
      'done',
      $result->getPostImportStatus()
    );
  }

  /**
   * @covers ::processItem
   */
  public function testProcessItem() {
    $data_identifier = 'test_identifier';

    $this->config('datastore.settings')
      ->set('drop_datastore_on_post_import_error', TRUE)
      ->save();

    // Our error result.
    $error_result = $this->getMockBuilder(PostImportResult::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['getPostImportStatus', 'storeResult'])
      ->getMock();
    $error_result->expects($this->any())
      ->method('getPostImportStatus')
      ->willReturn('error');
    $error_result->expects($this->once())
      ->method('storeResult');

    // Mock a logger to expect error logging.
    $logger = $this->getMockBuilder(LoggerChannelInterface::class)
      ->onlyMethods(['error', 'notice'])
      ->getMockForAbstractClass();
    // Never expect an error.
    $logger->expects($this->never())
      ->method('error');
    // Expect one notice.
    $logger->expects($this->once())
      ->method('notice')
      ->with(
        'Successfully dropped the datastore for resource @identifier due to a post import error. Visit the Datastore Import Status dashboard for details.',
        ['@identifier' => $data_identifier],
      );
    $this->container->set('dkan.datastore.logger_channel', $logger);

    // Datastore service will always succeed. Mocked so we don't have to deal
    // with dropping an actual datastore.
    $datastore_service = $this->getMockBuilder(DatastoreService::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['drop'])
      ->getMock();
    $datastore_service->expects($this->once())
      ->method('drop');
    // Put the service into the service container.
    $this->container->set('dkan.datastore.service', $datastore_service);

    // Return our error result.
    $post_import_resource_processor = $this->getMockBuilder(PostImportResourceProcessor::class)
      ->setConstructorArgs([
        [],
        '',
        ['cron' => ['lease_time' => 10800]],
        $this->container->get('config.factory'),
        $this->container->get('dkan.datastore.data_dictionary.alter_table_query_builder.mysql'),
        $this->container->get('dkan.datastore.logger_channel'),
        $this->container->get('dkan.metastore.resource_mapper'),
        $this->container->get('dkan.datastore.service.resource_processor_collector'),
        $this->container->get('dkan.datastore.service'),
        $this->container->get('dkan.datastore.service.post_import'),
        $this->container->get('dkan.metastore.data_dictionary_discovery'),
        $this->container->get('dkan.metastore.reference_lookup'),
      ])
      ->onlyMethods(['postImportProcessItem'])
      ->getMock();
    $post_import_resource_processor->expects($this->once())
      ->method('postImportProcessItem')
      ->willReturn($error_result);

    // Data we'll pass to our method under test.
    $data = $this->getMockBuilder(DataResource::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['getIdentifier'])
      ->getMock();
    $data->expects($this->once())
      ->method('getIdentifier')
      ->willReturn($data_identifier);

    $post_import_resource_processor->processItem($data);
  }

  /**
   * @covers ::processItem
   */
  public function testProcessItemExceptionPath() {
    $this->config('datastore.settings')
      ->set('drop_datastore_on_post_import_error', TRUE)
      ->save();

    // Our error result.
    $error_result = $this->getMockBuilder(PostImportResult::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['getPostImportStatus', 'storeResult'])
      ->getMock();
    $error_result->expects($this->any())
      ->method('getPostImportStatus')
      ->willReturn('error');
    $error_result->expects($this->once())
      ->method('storeResult');

    // Mock a logger to expect error logging.
    $logger = $this->getMockBuilder(LoggerChannelInterface::class)
      ->onlyMethods(['error', 'notice'])
      ->getMockForAbstractClass();
    // Expect an error.
    $logger->expects($this->once())
      ->method('error');
    // Expect no notices.
    $logger->expects($this->never())
      ->method('notice');
    $this->container->set('dkan.datastore.logger_channel', $logger);

    // Datastore service rigged to explode.
    $datastore_service = $this->getMockBuilder(DatastoreService::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['drop'])
      ->getMock();
    $datastore_service->expects($this->once())
      ->method('drop')
      ->willThrowException(new \Exception('our test message'));
    // Put the service into the service container.
    $this->container->set('dkan.datastore.service', $datastore_service);

    // Return our error result.
    $post_import_resource_processor = $this->getMockBuilder(PostImportResourceProcessor::class)
      ->setConstructorArgs([
        [],
        '',
        ['cron' => ['lease_time' => 10800]],
        $this->container->get('config.factory'),
        $this->container->get('dkan.datastore.data_dictionary.alter_table_query_builder.mysql'),
        $this->container->get('dkan.datastore.logger_channel'),
        $this->container->get('dkan.metastore.resource_mapper'),
        $this->container->get('dkan.datastore.service.resource_processor_collector'),
        $this->container->get('dkan.datastore.service'),
        $this->container->get('dkan.datastore.service.post_import'),
        $this->container->get('dkan.metastore.data_dictionary_discovery'),
        $this->container->get('dkan.metastore.reference_lookup'),
      ])
      ->onlyMethods(['postImportProcessItem'])
      ->getMock();
    $post_import_resource_processor->expects($this->once())
      ->method('postImportProcessItem')
      ->willReturn($error_result);

    // Data we'll pass to our method under test.
    $data = $this->getMockBuilder(DataResource::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['getIdentifier'])
      ->getMock();
    $data->expects($this->once())
      ->method('getIdentifier')
      ->willReturn('test');

    $post_import_resource_processor->processItem($data);
  }

}
