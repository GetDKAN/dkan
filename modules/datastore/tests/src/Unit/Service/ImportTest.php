<?php

namespace Drupal\Tests\datastore\Unit\Service;

use Drupal\common\Storage\JobStoreFactory;
use Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Drupal\Core\DependencyInjection\Container;
use Drupal\Core\Logger\LoggerChannel;
use Drupal\Core\Logger\LoggerChannelFactory;
use PHPUnit\Framework\TestCase;
use Drupal\datastore\Service\Import as Service;
use Drupal\common\Resource;
use MockChain\Chain;
use Drupal\common\Storage\JobStore;
use Drupal\datastore\Storage\DatabaseTableFactory;
use Drupal\datastore\Storage\DatabaseTable;
use Procrastinator\Result;
use Dkan\Datastore\Importer;
use Dkan\Datastore\Resource as DatastoreResource;

/**
 *
 */
class ImportTest extends TestCase {

  /**
   *
   */
  public function test() {
    $container = (new Chain($this))
      ->add(\Symfony\Component\DependencyInjection\Container::class, 'get', ContainerAwareEventDispatcher::class)
      ->getMock();

    \Drupal::setContainer($container);

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

    $this->assertTrue($service->getResult() instanceof Result);
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

    $expectedLogError = "Error importing resource id:%id path:%path";

    $this->assertEquals($expectedLogError, $containerChain->getStoredInput('errors')[0]);
  }

}
