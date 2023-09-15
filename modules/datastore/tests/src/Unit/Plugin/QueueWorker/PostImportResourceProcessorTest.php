<?php

namespace Drupal\Tests\datastore\Unit\Plugin\QueueWorker;

use Drupal\Core\DependencyInjection\Container;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\StreamWrapper\PublicStream;
use Drupal\Core\StreamWrapper\StreamWrapperManager;

use Drupal\common\DataResource;
use Drupal\datastore\DataDictionary\AlterTableQueryBuilderInterface;
use Drupal\datastore\DataDictionary\AlterTableQueryInterface;
use Drupal\datastore\Plugin\QueueWorker\PostImportResourceProcessor;
use Drupal\datastore\Service\ResourceProcessorInterface;
use Drupal\datastore\Service\ResourceProcessorCollector;
use Drupal\metastore\DataDictionary\DataDictionaryDiscovery;
use Drupal\metastore\DataDictionary\DataDictionaryDiscoveryInterface;
use Drupal\metastore\ResourceMapper;
use Drupal\datastore\service\PostImport;
use Drupal\metastore\MetastoreService;

use MockChain\Chain;
use MockChain\Options;
use PHPUnit\Framework\TestCase;
use RootedData\RootedJsonData;

/**
 * Test \Drupal\datastore\Plugin\QueueWorker\PostImportResourceProcessor.
 */
class PostImportResourceProcessorTest extends TestCase {

  /**
   * HTTP host protocol and domain for testing download URL.
   *
   * @var string
   */
  protected const HOST = 'http://example.com';

  /**
   * Test postImportProcessItem() succeeds.
   */
  public function testPostImportProcessItem() {
    $resource = new DataResource('test.csv', 'text/csv');

    $dataDictionaryDiscovery = $this->getMockBuilder(DataDictionaryDiscovery::class)
    ->onlyMethods(['getDataDictionaryMode'])
    ->disableOriginalConstructor()
    ->getMock();

    $dataDictionaryDiscovery->method('getDataDictionaryMode')
    ->willReturn("sitewide");

    $resource_processor = (new Chain($this))
      ->add(ResourceProcessorInterface::class, 'process')
      ->getMock();

    $container_chain = $this->getContainerChain()
      ->add(ResourceProcessorCollector::class, 'getResourceProcessors', [$resource_processor])
      ->add(ResourceMapper::class, 'get', $resource);
    \Drupal::setContainer($container_chain->getMock());

    $dictionaryEnforcer = PostImportResourceProcessor::create(
       $container_chain->getMock(), [], '', ['cron' => ['lease_time' => 10800]]
    );

    $postImportResult = $dictionaryEnforcer->postImportProcessItem($resource);

    $this->assertEquals($dataDictionaryDiscovery->getDataDictionaryMode(), DataDictionaryDiscoveryInterface::MODE_SITEWIDE);
    $this->assertEquals($resource->getIdentifier(), $postImportResult->getResourceIdentifier());
    $this->assertEquals($resource->getVersion(), $postImportResult->getResourceVersion());
    $this->assertEquals('done', $postImportResult->getPostImportStatus());
    $this->assertEquals(NULL, $postImportResult->getPostImportMessage());

    // Ensure resources were processed.
    $notices = $container_chain->getStoredInput('notice');
    $this->assertEmpty($notices);
    // Ensure no exceptions were thrown.
    $errors = $container_chain->getStoredInput('error');
    $this->assertEmpty($errors);
  }

  /**
   * Test postImportProcessItem() DataDictionary disabled.
   */
  public function testPostImportProcessItemDataDictionaryDisabled() {
    $resource = new DataResource('test.csv', 'text/csv');

    $dataDictionaryDiscovery = $this->getMockBuilder(DataDictionaryDiscovery::class)
    ->onlyMethods(['getDataDictionaryMode'])
    ->disableOriginalConstructor()
    ->getMock();

    $dataDictionaryDiscovery->method('getDataDictionaryMode')
    ->willReturn("none");

    $resource_processor = (new Chain($this))
      ->add(ResourceProcessorInterface::class, 'process')
      ->getMock();

    $container_chain = $this->getContainerChain()
      ->add(ResourceProcessorCollector::class, 'getResourceProcessors', [$resource_processor])
      ->add(ResourceMapper::class, 'get', $resource)
      ->add(DataDictionaryDiscoveryInterface::class, 'getDataDictionaryMode', 'none')
      ->add(DataDictionaryDiscovery::class, 'getDataDictionaryMode', 'none');
    \Drupal::setContainer($container_chain->getMock());

    $dictionaryEnforcer = PostImportResourceProcessor::create(
       $container_chain->getMock(), [], '', ['cron' => ['lease_time' => 10800]]
    );

    $postImportResult = $dictionaryEnforcer->postImportProcessItem($resource);

    $this->assertEquals($dataDictionaryDiscovery->getDataDictionaryMode(), DataDictionaryDiscoveryInterface::MODE_NONE);
    $this->assertEquals($resource->getIdentifier(), $postImportResult->getResourceIdentifier());
    $this->assertEquals($resource->getVersion(), $postImportResult->getResourceVersion());
    $this->assertEquals('N/A', $postImportResult->getPostImportStatus());
    $this->assertEquals('Data-Dictionary Disabled', $postImportResult->getPostImportMessage());
  }

  /**
   * Test postImportProcessItem() halts and logs a message if a resource no longer exists.
   */
  public function testPostImportProcessItemResourceNoLongerExists() {
    $resource = new DataResource('test.csv', 'text/csv');

    $resource_processor = (new Chain($this))
      ->add(ResourceProcessorInterface::class, 'process')
      ->getMock();

    $container_chain = $this->getContainerChain()
      ->add(ResourceProcessorCollector::class, 'getResourceProcessors', [$resource_processor])
      ->add(ResourceMapper::class, 'get', NULL);
    \Drupal::setContainer($container_chain->getMock());

    $dictionaryEnforcer = PostImportResourceProcessor::create(
       $container_chain->getMock(), [], '', ['cron' => ['lease_time' => 10800]]
    );

    $postImportResult = $dictionaryEnforcer->postImportProcessItem($resource);

    $this->assertEquals($resource->getIdentifier(), $postImportResult->getResourceIdentifier());
    $this->assertEquals($resource->getVersion(), $postImportResult->getResourceVersion());
    $this->assertEquals('error', $postImportResult->getPostImportStatus());
    $this->assertEquals('Cancelling resource processing; resource no longer exists.', $postImportResult->getPostImportMessage());

    // Ensure notice was logged and resource processing was halted.
    $notices = $container_chain->getStoredInput('notice');
    $this->assertEquals($notices[0], 'Cancelling resource processing; resource no longer exists.');
    // Ensure no exceptions were thrown.
    $errors = $container_chain->getStoredInput('error');
    $this->assertEmpty($errors);
  }

  // /**
  //  * Test postImportProcessItem() halts and logs a message if a resource has changed.
  //  */
  public function testPostImportProcessItemResourceChanged() {
    $resource_a = new DataResource('test.csv', 'text/csv');

    $resource_b = (new DataResource('test2.csv', 'text/csv'))->createNewVersion();

    $resource_processor = (new Chain($this))
      ->add(ResourceProcessorInterface::class, 'process')
      ->getMock();

    $container_chain = $this->getContainerChain()
      ->add(ResourceProcessorCollector::class, 'getResourceProcessors', [$resource_processor])
      ->add(ResourceMapper::class, 'get', $resource_a);
    \Drupal::setContainer($container_chain->getMock());

    $dictionaryEnforcer = PostImportResourceProcessor::create(
       $container_chain->getMock(), [], '', ['cron' => ['lease_time' => 10800]]
    );

    $postImportResult = $dictionaryEnforcer->postImportProcessItem($resource_b);

    $this->assertEquals($resource_b->getIdentifier(), $postImportResult->getResourceIdentifier());
    $this->assertEquals($resource_b->getVersion(), $postImportResult->getResourceVersion());
    $this->assertEquals('error', $postImportResult->getPostImportStatus());
    $this->assertEquals('Cancelling resource processing; resource has changed.', $postImportResult->getPostImportMessage());

    // Ensure notice was logged and resource processing was halted.
    $notices = $container_chain->getStoredInput('notice');
    $this->assertEquals($notices[0], 'Cancelling resource processing; resource has changed.');
    // Ensure no exceptions were thrown.
    $errors = $container_chain->getStoredInput('error');
    $this->assertEmpty($errors);
  }

  // /**
  //  * Test postImportProcessItem() logs errors encountered in processors.
  //  */
  public function testPostImportProcessItemProcessorError() {
    $resource = new DataResource('test.csv', 'text/csv');

    $resource_processor = (new Chain($this))
      ->add(ResourceProcessorInterface::class, 'process', new \Exception('Test Error'))
      ->getMock();

    $container_chain = $this->getContainerChain()
      ->add(ResourceProcessorCollector::class, 'getResourceProcessors', [$resource_processor])
      ->add(ResourceMapper::class, 'get', $resource);
    \Drupal::setContainer($container_chain->getMock());

    $dictionaryEnforcer = PostImportResourceProcessor::create(
       $container_chain->getMock(), [], '', ['cron' => ['lease_time' => 10800]]
    );

    $postImportResult = $dictionaryEnforcer->postImportProcessItem($resource);

    $this->assertEquals($resource->getIdentifier(), $postImportResult->getResourceIdentifier());
    $this->assertEquals($resource->getVersion(), $postImportResult->getResourceVersion());
    $this->assertEquals('error', $postImportResult->getPostImportStatus());
    $this->assertEquals('Test Error', $postImportResult->getPostImportMessage());

    // Ensure resources were processed.
    $notices = $container_chain->getStoredInput('notice');
    $this->assertEmpty($notices);
    // Ensure test error was caught.
    $errors = $container_chain->getStoredInput('error');
    $this->assertEquals($errors[0], 'Test Error');
  }

  /**
   * Get container chain.
   */
  protected function getContainerChain() {

    $options = (new Options())
      ->add('dkan.datastore.data_dictionary.alter_table_query_builder.mysql', AlterTableQueryBuilderInterface::class)
      ->add('dkan.metastore.data_dictionary_discovery', DataDictionaryDiscovery::class)
      ->add('logger.factory', LoggerChannelFactoryInterface::class)
      ->add('dkan.metastore.service', MetastoreService::class)
      ->add('dkan.metastore.data_dictionary_discovery', DataDictionaryDiscoveryInterface::class)
      ->add('stream_wrapper_manager', StreamWrapperManager::class)
      ->add('dkan.metastore.resource_mapper', ResourceMapper::class)
      ->add('dkan.datastore.service.resource_processor_collector', ResourceProcessorCollector::class)
      ->add('dkan.datastore.service.post_import', PostImport::class)
      ->index(0);

    $json = '{"identifier":"foo","title":"bar","data":{"fields":[]}}';

    return (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(LoggerChannelFactoryInterface::class, 'get', LoggerChannelInterface::class)
      ->add(LoggerChannelInterface::class, 'error', NULL, 'error')
      ->add(LoggerChannelInterface::class, 'notice', NULL, 'notice')
      ->add(MetastoreService::class, 'get', new RootedJsonData($json))
      ->add(AlterTableQueryBuilderInterface::class, 'setConnectionTimeout', AlterTableQueryBuilderInterface::class)
      ->add(AlterTableQueryBuilderInterface::class, 'getQuery', AlterTableQueryInterface::class)
      ->add(DataDictionaryDiscoveryInterface::class, 'dictionaryIdFromResource', 'resource_id')
      ->add(PublicStream::class, 'getExternalUrl', self::HOST)
      ->add(StreamWrapperManager::class, 'getViaUri', PublicStream::class)
      ->add(ResourceMapper::class, 'get', DataResource::class);
  }

}
