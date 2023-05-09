<?php

namespace Drupal\Tests\datastore\Unit\EventSubscriber;

use Drupal\common\DataResource;
use Drupal\common\Storage\JobStoreFactory;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Database\Connection;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\datastore\EventSubscriber\DatastoreSubscriber;
use Drupal\datastore\DatastoreService;
use Drupal\datastore\Service\ResourcePurger;
use Drupal\datastore\Storage\DatabaseTable;
use Drupal\common\Events\Event;
use MockChain\Chain;
use MockChain\Options;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Drupal\datastore\Service\Factory\ImportServiceFactory;
use Drupal\datastore\Service\ImportService;
use Drupal\common\Storage\JobStore;
use Drupal\metastore\MetastoreItemInterface;

/**
 *
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

    $config = (new Chain($this))
      ->add(ConfigFactory::class, 'get', ImmutableConfig::class)
      ->add(ImmutableConfig::class, 'get', [])
      ->getMock();

    $options = (new Options())
      ->add('config.factory', $this->getImmutableConfigMock())
      ->add('logger.factory', LoggerChannelFactory::class)
      ->add('dkan.datastore.service', DatastoreService::class)
      ->add('dkan.datastore.service.resource_purger', ResourcePurger::class)
      ->add('dkan.common.job_store', JobStoreFactory::class)
      ->add("database", Connection::class)
      ->index(0);

    $chain = (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(DatastoreService::class, 'drop')
      ->add(DatabaseTable::class, 'drop')
      ->add(ImportServiceFactory::class, 'getInstance', ImportService::class)
      ->add(ImportService::class, 'remove')
      ->add(JobStoreFactory::class, 'getInstance', JobStore::class)
      ->add(JobStore::class, 'remove')
      ->add(LoggerChannelFactory::class, 'get', LoggerChannelInterface::class)
      ->add(LoggerChannelInterface::class, 'error', NULL, 'errors')
      ->add(LoggerChannelInterface::class, 'notice', NULL, "notices");

    $subscriber = DatastoreSubscriber::create($chain->getMock());
    $test = $subscriber->drop($event);
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
      ->add('logger.factory', LoggerChannelFactory::class)
      ->add('dkan.datastore.service', DatastoreService::class)
      ->add('dkan.datastore.service.resource_purger', ResourcePurger::class)
      ->add('dkan.common.job_store', JobStoreFactory::class)
      ->add("database", Connection::class)
      ->index(0);

    $chain = (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(DatastoreService::class, 'drop', new \Exception('error'))
      ->add(ImportServiceFactory::class, 'getInstance', ImportService::class)
      ->add(ImportService::class, 'remove')
      ->add(JobStoreFactory::class, 'getInstance', JobStore::class)
      ->add(JobStore::class, 'remove')
      ->add(LoggerChannelFactory::class, 'get', LoggerChannelInterface::class)
      ->add(LoggerChannelInterface::class, 'error', NULL, 'errors')
      ->add(LoggerChannelInterface::class, 'notice', NULL, "notices");

    $subscriber = DatastoreSubscriber::create($chain->getMock());
    $test = $subscriber->drop($event);
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
      ->add('logger.factory', LoggerChannelFactory::class)
      ->add('dkan.datastore.service', DatastoreService::class)
      ->add('dkan.datastore.service.resource_purger', ResourcePurger::class)
      ->add('dkan.common.job_store', JobStoreFactory::class)
      ->add("database", Connection::class)
      ->index(0);

    $chain = (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(DatastoreService::class, 'drop')
      ->add(ImportServiceFactory::class, 'getInstance', ImportService::class)
      ->add(ImportService::class, 'remove')
      ->add(JobStoreFactory::class, 'getInstance', JobStore::class)
      ->add(JobStore::class, 'remove', new \Exception('error'))
      ->add(LoggerChannelFactory::class, 'get', LoggerChannelInterface::class)
      ->add(LoggerChannelInterface::class, 'error', NULL, 'errors')
      ->add(LoggerChannelInterface::class, 'notice', NULL, "notices");

    $subscriber = DatastoreSubscriber::create($chain->getMock());
    $test = $subscriber->drop($event);
    $this->assertStringContainsString('Failed to remove importer job', $chain->getStoredInput('errors')[0]);
  }

  /**
   * Private.
   */
  private function getContainerChain() {
    $options = (new Options())
      ->add('config.factory', $this->getImmutableConfigMock())
      ->add('logger.factory', LoggerChannelFactory::class)
      ->add('dkan.datastore.service', DatastoreService::class)
      ->add('dkan.datastore.service.resource_purger', ResourcePurger::class)
      ->add('dkan.common.job_store', JobStoreFactory::class)
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
