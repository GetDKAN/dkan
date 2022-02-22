<?php

namespace Drupal\Tests\datastore\Unit\Service;

use Drupal\Core\DependencyInjection\Container;
use Drupal\Core\Logger\LoggerChannel;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\StreamWrapper\PublicStream;
use Drupal\Core\StreamWrapper\StreamWrapperManager;

use Drupal\common\Resource;
use Drupal\common\Storage\JobStore;
use Drupal\common\Storage\JobStoreFactory;
use Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Drupal\datastore\Service\Import as Service;
use Drupal\datastore\Storage\DatabaseTableFactory;
use Drupal\datastore\Storage\DatabaseTable;

use Dkan\Datastore\Importer;
use Dkan\Datastore\Resource as DatastoreResource;
use MockChain\Options;
use MockChain\Chain;
use PHPUnit\Framework\TestCase;
use Procrastinator\Result;

/**
 *
 */
class ImportTest extends TestCase {

  /**
   * Host protocol and domain for testing file path and download URL.
   *
   * @var string
   */
  const HOST = 'http://h-o.st';

  /**
   *
   */
  public function test() {
    $options = (new Options())
      ->add('event_dispatcher', ContainerAwareEventDispatcher::class)
      ->add('request_stack', RequestStack::class)
      ->add('stream_wrapper_manager', StreamWrapperManager::class)
      ->index(0);
    $container_chain = (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(StreamWrapperManager::class, 'getViaUri', PublicStream::class)
      ->add(PublicStream::class, 'getExternalUrl', self::HOST);

    \Drupal::setContainer($container_chain->getMock());

    $resource = new Resource("http://hello.goodby/text.csv", "text/csv");

    $jobStore = (new Chain($this))
      ->add(JobStore::class, "retrieve", "")
      ->add(Importer::class, "run", Result::class)
      ->add(Importer::class, "getResult", Result::class)
      ->add(JobStore::class, "store", "")
      ->getMock();

    $databaseTableFactory = (new Chain($this))
      ->add(DatabaseTableFactory::class, "getInstance", DatabaseTable::class)
      ->getMock();

    $jobStoreFactory = (new Chain($this))
      ->add(JobStoreFactory::class, "getInstance", $jobStore)
      ->getMock();

    $service = new Service($resource, $jobStoreFactory, $databaseTableFactory);
    $service->import();

    $result = $service->getResult();
    $this->assertTrue($result instanceof Result);
    $this->assertEmpty($result->getError());

  }

  /**
   *
   */
  public function testLogImportError() {
    $importMock = (new Chain($this))
      ->add(Service::class, 'initializeResource')
      ->add(Service::class, 'getResource', DatastoreResource::class)
      ->add(Service::class, 'getImporter', Importer::class)
      ->add(Importer::class, 'run', Result::class)
      ->add(Service::class, 'getResult', Result::class)
      ->add(Result::class, 'getStatus', Result::ERROR)
      ->add(DatastoreResource::class, 'getId', 'abc')
      ->add(DatastoreResource::class, 'getFilePath', 'some/path/file.csv')
      ->getMock();

    $containerChain = (new Chain($this))
      ->add(Container::class, 'get', LoggerChannelFactory::class)
      ->add(LoggerChannelFactory::class, 'get', LoggerChannel::class)
      ->add(LoggerChannel::class, 'error', NULL, 'errors');
    $container = $containerChain->getMock();

    \Drupal::setContainer($container);

    $importMock->import();

    $expectedLogError = 'Error importing resource id:%id path:%path message:%message';

    $this->assertEquals($expectedLogError, $containerChain->getStoredInput('errors')[0]);
  }

}
