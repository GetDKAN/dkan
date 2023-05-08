<?php

namespace Drupal\Tests\datastore\Unit\Service;

use Drupal\Core\DependencyInjection\Container;
use Drupal\Core\Logger\LoggerChannel;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\StreamWrapper\PublicStream;
use Drupal\Core\StreamWrapper\StreamWrapperManager;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\Queue\QueueInterface;
use Drupal\metastore\DataDictionary\DataDictionaryDiscoveryInterface;

use Drupal\common\DataResource;
use Drupal\common\Storage\JobStore;
use Drupal\common\Storage\JobStoreFactory;
use Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Drupal\datastore\Service\ImportService;
use Drupal\datastore\Storage\DatabaseTableFactory;
use Drupal\datastore\Storage\DatabaseTable;

use Drupal\datastore\Plugin\QueueWorker\ImportJob;
use MockChain\Options;
use MockChain\Chain;
use PHPUnit\Framework\TestCase;
use Procrastinator\Result;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @coversDefaultClass \Drupal\datastore\Service\ImportService
 */
class ImportServiceTest extends TestCase {

  /**
   * Host protocol and domain for testing file path and download URL.
   *
   * @var string
   */
  const HOST = 'http://h-o.st';

  /**
   *
   */
  public function testImport() {
    $options = (new Options())
      ->add('event_dispatcher', ContainerAwareEventDispatcher::class)
      ->add('request_stack', RequestStack::class)
      ->add('stream_wrapper_manager', StreamWrapperManager::class)
      ->add('queue', QueueFactory::class)
      ->index(0);
    $container_chain = (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(StreamWrapperManager::class, 'getViaUri', PublicStream::class)
      ->add(PublicStream::class, 'getExternalUrl', self::HOST)
      ->add(QueueFactory::class, 'get', QueueInterface::class)
      ->add(QueueInterface::class, 'createItem', NULL, 'items');

    \Drupal::setContainer($container_chain->getMock());

    $resource = new DataResource("http://hello.goodby/text.csv", "text/csv");

    $result = (new Chain($this))
      ->add(Result::class, 'getStatus', Result::DONE)
      ->getMock();

    $jobStore = (new Chain($this))
      ->add(JobStore::class, "retrieve", "")
      ->add(ImportJob::class, "run", Result::class)
      ->add(ImportJob::class, "getResult", $result)
      ->add(JobStore::class, "store", "")
      ->getMock();

    $databaseTableFactory = (new Chain($this))
      ->add(DatabaseTableFactory::class, "getInstance", DatabaseTable::class)
      ->getMock();

    $jobStoreFactory = (new Chain($this))
      ->add(JobStoreFactory::class, "getInstance", $jobStore)
      ->getMock();

    $service = new ImportService($resource, $jobStoreFactory, $databaseTableFactory);
    $service->import();

    $result = $service->getResult();
    $this->assertTrue($result instanceof Result);
    $this->assertEmpty($result->getError());
  }

  /**
   * Test a dictionary enforcer queue job is created on success when enabled.
   */
  public function testDictEnforcerQueuedOnSuccess() {
    $datastore_table = 'datastore_test';
    $resource = new DataResource('abc.txt', 'text/csv');

    $options = (new Options())
      ->add('dkan.metastore.data_dictionary_discovery', DataDictionaryDiscoveryInterface::class)
      ->add('logger', LoggerChannelFactory::class)
      ->add('queue', QueueFactory::class)
      ->index(0);
    $containerChain = (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(LoggerChannelFactory::class, 'get', LoggerChannel::class)
      ->add(LoggerChannel::class, 'error', NULL, 'errors')
      ->add(QueueFactory::class, 'get', QueueInterface::class)
      ->add(QueueInterface::class, 'createItem', NULL, 'items')
      ->add(DataDictionaryDiscoveryInterface::class, 'getDataDictionaryMode', DataDictionaryDiscoveryInterface::MODE_SITEWIDE);
    $container = $containerChain->getMock();
    \Drupal::setContainer($container);

    $importService = (new Chain($this))
      ->add(ImportService::class, 'getResource', DataResource::class)
      ->add(ImportService::class, 'getImporter', ImportJob::class)
      ->add(ImportService::class, 'getStorage', DatabaseTable::class)
      ->add(ImportService::class, 'getResource',  $resource)
      ->add(ImportJob::class, 'run', Result::class)
      ->add(ImportService::class, 'getResult', Result::class)
      ->add(Result::class, 'getStatus', Result::DONE)
      ->add(DatabaseTable::class, 'getTableName', $datastore_table)
      ->getMock();
    $importService->import();

    // Validate that a dictionary enforcer queue item was created.
    $this->assertEquals([$resource], $containerChain->getStoredInput('items'));
  }

  /**
   *
   */
  public function testLogImportError() {
    $importMock = (new Chain($this))
      ->add(ImportService::class, 'getResource', new DataResource('abc.txt', 'text/csv'))
      ->add(ImportService::class, 'getImporter', ImportJob::class)
      ->add(ImportJob::class, 'run', Result::class)
      ->add(ImportService::class, 'getResult', Result::class)
      ->add(Result::class, 'getStatus', Result::ERROR)
      ->getMock();

    // Construct and set `\Drupal::container` mock.
    $options = (new Options())
      ->add('stream_wrapper_manager', StreamWrapperManager::class)
      ->add('logger.factory', LoggerChannelFactory::class)
      ->index(0);

    $containerChain = (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(LoggerChannelFactory::class, 'get', LoggerChannel::class)
      ->add(LoggerChannel::class, 'error', NULL, 'errors')
      ->add(StreamWrapperManager::class, 'getViaUri', PublicStream::class)
      ->add(PublicStream::class, 'getExternalUrl', self::HOST);
    $container = $containerChain->getMock();

    \Drupal::setContainer($container);

    $importMock->import();

    $expectedLogError = 'Error importing resource id:%id path:%path message:%message';

    $this->assertEquals($expectedLogError, $containerChain->getStoredInput('errors')[0]);
  }

}
