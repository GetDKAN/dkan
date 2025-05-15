<?php

namespace Drupal\Tests\datastore\Unit\EventSubscriber;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Database\Connection;
use Drupal\common\DataResource;
use Drupal\common\Events\Event;
use Drupal\common\Storage\JobStore;
use Drupal\common\Storage\JobStoreFactory;
use Drupal\datastore\DatastoreService;
use Drupal\datastore\EventSubscriber\DatastoreSubscriber;
use Drupal\datastore\Service\Factory\ImportServiceFactory;
use Drupal\datastore\Service\ImportService;
use Drupal\datastore\Service\ResourcePurger;
use Drupal\datastore\Storage\DatabaseTable;
use Drupal\datastore\Storage\ImportJobStoreFactory;
use Drupal\metastore\MetastoreItemInterface;
use MockChain\Chain;
use MockChain\Options;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @coversDefaultClass \Drupal\datastore\EventSubscriber\DatastoreSubscriber
 *
 * @group dkan
 * @group datastore
 * @group unit
 */
class DatastoreSubscriberTest extends TestCase {

  /**
   *
   */
  public function test() {
    $url = 'http://hello.world/file.csv';
    $resource = new DataResource($url, 'text/csv');
    $event = new Event($resource);

    $chain = $this->getContainerChain();

    // When the conditions of a new "datastoreable" resource are met, add
    // an import operation to the queue.
    $subscriber = DatastoreSubscriber::create($chain->getMock());
    $subscriber->onRegistration($event);

    // The resource identifier is registered with the datastore service.
    $this->assertEquals(md5($url), $chain->getStoredInput('import')[0]);
  }

  /**
   * Test Registration.
   */
  public function testOnRegistrationException() {
    $url = 'http://hello.world/file.csv';
    $resource = new DataResource($url, 'text/csv');
    $event = new Event($resource);

    $chain = $this->getContainerChain();
    $chain->add(DatastoreService::class, 'import', new \Exception());

    // When the conditions of a new "datastoreable" resource are met, add
    // an import operation to the queue.
    $subscriber = DatastoreSubscriber::create($chain->getMock());
    $subscriber->onRegistration($event);

    // Doing it all for the coverage.
    $this->assertTrue(TRUE);
  }

  /**
   * Test ResourcePurger-related parts.
   */
  public function testResourcePurging() {

    $mockDatasetPublication = (new Chain($this))
      ->add(Event::class, 'getData', MetastoreItemInterface::class)
      ->getMock();

    $chain = $this->getContainerChain();
    $chain->add(MetastoreItemInterface::class, 'getIdentifier', 1);

    $subscriber = DatastoreSubscriber::create($chain->getMock());
    $voidReturn = $subscriber->purgeResources($mockDatasetPublication);
    $this->assertNull($voidReturn);
  }

  /**
   * Test drop.
   */
  public function testDrop() {
    $url = 'http://hello.world/file.csv';
    $resource = new DataResource($url, 'text/csv');
    $event = new Event($resource);

    (new Chain($this))
      ->add(ConfigFactory::class, 'get', ImmutableConfig::class)
      ->add(ImmutableConfig::class, 'get', [])
      ->getMock();

    $options = (new Options())
      ->add('config.factory', $this->getImmutableConfigMock())
      ->add('dkan.datastore.logger_channel', LoggerInterface::class)
      ->add('dkan.datastore.service', DatastoreService::class)
      ->add('dkan.datastore.service.resource_purger', ResourcePurger::class)
      ->add('dkan.common.job_store', JobStoreFactory::class)
      ->add('dkan.datastore.import_job_store_factory', ImportJobStoreFactory::class)
      ->add("database", Connection::class)
      ->add('event_dispatcher', EventDispatcherInterface::class)
      ->index(0);

    $chain = (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(DatastoreService::class, 'drop')
      ->add(DatabaseTable::class, 'drop')
      ->add(ImportServiceFactory::class, 'getInstance', ImportService::class)
      ->add(ImportService::class, 'remove')
      ->add(JobStoreFactory::class, 'getInstance', JobStore::class)
      ->add(ImportJobStoreFactory::class, 'getInstance', JobStore::class)
      ->add(JobStore::class, 'remove')
      ->add(LoggerInterface::class, 'error', NULL, 'errors')
      ->add(LoggerInterface::class, 'notice', NULL, 'notices')
      ->add(EventDispatcherInterface::class, 'dispatch');

    $subscriber = DatastoreSubscriber::create($chain->getMock());
    $subscriber->drop($event);
    $this->assertStringContainsString('Dropping datastore', $chain->getStoredInput('notices')[0]);
    $this->assertEmpty($chain->getStoredInput('errors'));
  }

  /**
   * Test drop exception.
   */
  public function testDatastoreDropException() {
    $url = 'http://hello.world/file.csv';
    $resource = new DataResource($url, 'text/csv');
    $event = new Event($resource);

    $options = (new Options())
      ->add('config.factory', $this->getImmutableConfigMock())
      ->add('dkan.datastore.logger_channel', LoggerInterface::class)
      ->add('dkan.datastore.service', DatastoreService::class)
      ->add('dkan.datastore.service.resource_purger', ResourcePurger::class)
      ->add('dkan.common.job_store', JobStoreFactory::class)
      ->add('dkan.datastore.import_job_store_factory', ImportJobStoreFactory::class)
      ->add("database", Connection::class)
      ->add('event_dispatcher', EventDispatcherInterface::class)
      ->index(0);

    $chain = (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(DatastoreService::class, 'drop', new \Exception('error'))
      ->add(ImportServiceFactory::class, 'getInstance', ImportService::class)
      ->add(ImportService::class, 'remove')
      ->add(JobStoreFactory::class, 'getInstance', JobStore::class)
      ->add(ImportJobStoreFactory::class, 'getInstance', JobStore::class)
      ->add(JobStore::class, 'remove')
      ->add(LoggerInterface::class, 'error', NULL, 'errors')
      ->add(LoggerInterface::class, 'notice', NULL, 'notices')
      ->add(EventDispatcherInterface::class, 'dispatch');

    $subscriber = DatastoreSubscriber::create($chain->getMock());
    $subscriber->drop($event);
    $this->assertStringContainsString('Failed to drop', $chain->getStoredInput('errors')[0]);
  }

  /**
   * Test jobstore remove exception.
   */
  public function testJobStoreRemoveException() {
    $url = 'http://hello.world/file.csv';
    $resource = new DataResource($url, 'text/csv');
    $event = new Event($resource);

    $options = (new Options())
      ->add('config.factory', $this->getImmutableConfigMock())
      ->add('dkan.datastore.logger_channel', LoggerInterface::class)
      ->add('dkan.datastore.service', DatastoreService::class)
      ->add('dkan.datastore.service.resource_purger', ResourcePurger::class)
      ->add('dkan.common.job_store', JobStoreFactory::class)
      ->add('dkan.datastore.import_job_store_factory', ImportJobStoreFactory::class)
      ->add("database", Connection::class)
      ->add('event_dispatcher', EventDispatcherInterface::class)
      ->index(0);

    $chain = (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(DatastoreService::class, 'drop')
      ->add(ImportServiceFactory::class, 'getInstance', ImportService::class)
      ->add(ImportService::class, 'remove')
      ->add(JobStoreFactory::class, 'getInstance', JobStore::class)
      ->add(ImportJobStoreFactory::class, 'getInstance', JobStore::class)
      ->add(JobStore::class, 'remove', new \Exception('error'))
      ->add(LoggerInterface::class, 'error', NULL, 'errors')
      ->add(LoggerInterface::class, 'notice', NULL, 'notices')
      ->add(EventDispatcherInterface::class, 'dispatch');

    $subscriber = DatastoreSubscriber::create($chain->getMock());
    $subscriber->drop($event);
    $this->assertStringContainsString('Failed to remove importer job', $chain->getStoredInput('errors')[0]);
  }

  /**
   * Private.
   */
  private function getContainerChain() {
    $options = (new Options())
      ->add('config.factory', $this->getImmutableConfigMock())
      ->add('dkan.datastore.logger_channel', LoggerInterface::class)
      ->add('dkan.datastore.service', DatastoreService::class)
      ->add('dkan.datastore.service.resource_purger', ResourcePurger::class)
      ->add('dkan.common.job_store', JobStoreFactory::class)
      ->add('dkan.datastore.import_job_store_factory', ImportJobStoreFactory::class)
      ->add('event_dispatcher', EventDispatcherInterface::class)
      ->index(0);

    return (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(DatastoreService::class, 'import', [], 'import')
      ->add(ResourcePurger::class, 'schedule');
  }

  private function getImmutableConfigMock() {
    return (new Chain($this))
      ->add(ConfigFactory::class, 'get', ImmutableConfig::class)
      ->add(ImmutableConfig::class, 'get', [])
      ->getMock();
  }

}
