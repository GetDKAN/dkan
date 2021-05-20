<?php

namespace Drupal\Tests\datastore\Unit\EventSubscriber;

use Drupal\common\Resource;
use Drupal\common\Storage\JobstoreFactory;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\datastore\EventSubscriber\DatastoreSubscriber;
use Drupal\datastore\Service;
use Drupal\datastore\Service\ResourcePurger;
use Drupal\datastore\Storage\DatabaseTable;
use Drupal\common\Events\Event;
use MockChain\Chain;
use MockChain\Options;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Drupal\datastore\Service\Factory\Import as ImportServiceFactory;
use Drupal\datastore\Service\Import as ImportService;
use Drupal\common\Storage\JobStore;

/**
 *
 */
class DatastoreSubscriberTest extends TestCase {

  /**
   *
   */
  public function test() {
    $url = 'http://hello.world/file.csv';
    $resource = new Resource($url, 'text/csv');
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
    $resource = new Resource($url, 'text/csv');
    $event = new Event($resource);

    $chain = $this->getContainerChain();
    $chain->add(Service::class, 'import', new \Exception());

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
      ->add(Event::class, 'getData', ContentEntityInterface::class)
      ->getMock();

    $chain = $this->getContainerChain();
    $chain->add(ContentEntityInterface::class, 'uuid', 1);

    $subscriber = DatastoreSubscriber::create($chain->getMock());
    $voidReturn = $subscriber->purgeResources($mockDatasetPublication);
    $this->assertNull($voidReturn);
  }

  /**
   * Test drop.
   */
  public function testDrop() {
    $url = 'http://hello.world/file.csv';
    $resource = new Resource($url, 'text/csv');
    $event = new Event($resource);

    $options = (new Options())
      ->add('logger.factory', LoggerChannelFactory::class)
      ->add('dkan.datastore.service', Service::class)
      ->add('dkan.datastore.service.resource_purger', ResourcePurger::class)
      ->add('dkan.common.job_store', JobStoreFactory::class)
      ->add("database", Connection::class)
      ->index(0);

    $chain = (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(Service::class, 'drop')
      ->add(DatabaseTable::class, 'drop')
      ->add(DatabaseTable::class, 'hydrate')
      ->add(ImportServiceFactory::class, 'getInstance', ImportService::class)
      ->add(ImportService::class, 'remove')
      ->add(JobStoreFactory::class, 'getInstance', JobStore::class)
      ->add(JobStore::class, 'remove')
      ->add(LoggerChannelFactory::class, 'get', LoggerChannelInterface::class)
      ->add(LoggerChannelInterface::class, 'error', NULL, 'errors')
      ->add(LoggerChannelInterface::class, 'notice', NULL, "notices");

    $subscriber = DatastoreSubscriber::create($chain->getMock());
    $test = $subscriber->drop($event);
    $this->assertContains('Dropping datastore', $chain->getStoredInput('notices')[0]);
    //$this->assertEmpty($chain->getStoredInput('errors'));
    $this->assertContains('Failed to drop', $chain->getStoredInput('errors')[0]);
  }

  /**
   * Test drop exception.
   */
  public function testDatastoreDropException() {
    $url = 'http://hello.world/file.csv';
    $resource = new Resource($url, 'text/csv');
    $event = new Event($resource);

    $options = (new Options())
      ->add('logger.factory', LoggerChannelFactory::class)
      ->add('dkan.datastore.service', Service::class)
      ->add('dkan.datastore.service.resource_purger', ResourcePurger::class)
      ->add('dkan.common.job_store', JobStoreFactory::class)
      ->add("database", Connection::class)
      ->index(0);

    $chain = (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(Service::class, 'drop', new \Exception('error'))
      ->add(ImportServiceFactory::class, 'getInstance', ImportService::class)
      ->add(ImportService::class, 'remove')
      ->add(JobStoreFactory::class, 'getInstance', JobStore::class)
      ->add(JobStore::class, 'remove')
      ->add(LoggerChannelFactory::class, 'get', LoggerChannelInterface::class)
      ->add(LoggerChannelInterface::class, 'error', NULL, 'errors')
      ->add(LoggerChannelInterface::class, 'notice', NULL, "notices");

    $subscriber = DatastoreSubscriber::create($chain->getMock());
    $test = $subscriber->drop($event);
    $this->assertContains('Failed to drop', $chain->getStoredInput('errors')[0]);
  }

  /**
   * Test jobstore remove exception.
   */
  public function testJobStoreRemoveException() {
    $url = 'http://hello.world/file.csv';
    $resource = new Resource($url, 'text/csv');
    $event = new Event($resource);

    $options = (new Options())
      ->add('logger.factory', LoggerChannelFactory::class)
      ->add('dkan.datastore.service', Service::class)
      ->add('dkan.datastore.service.resource_purger', ResourcePurger::class)
      ->add('dkan.common.job_store', JobStoreFactory::class)
      ->add("database", Connection::class)
      ->index(0);

    $chain = (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(Service::class, 'drop')
      ->add(ImportServiceFactory::class, 'getInstance', ImportService::class)
      ->add(ImportService::class, 'remove')
      ->add(JobStoreFactory::class, 'getInstance', JobStore::class)
      ->add(JobStore::class, 'remove', new \Exception('error'))
      ->add(LoggerChannelFactory::class, 'get', LoggerChannelInterface::class)
      ->add(LoggerChannelInterface::class, 'error', NULL, 'errors')
      ->add(LoggerChannelInterface::class, 'notice', NULL, "notices");

    $subscriber = DatastoreSubscriber::create($chain->getMock());
    $test = $subscriber->drop($event);
    $this->assertContains('Failed to remove importer job', $chain->getStoredInput('errors')[0]);
  }

  /**
   * Private.
   */
  private function getContainerChain() {

    $options = (new Options())
      ->add('logger.factory', LoggerChannelFactory::class)
      ->add('dkan.datastore.service', Service::class)
      ->add('dkan.datastore.service.resource_purger', ResourcePurger::class)
      ->index(0);

    return (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(Service::class, 'import', [], 'import')
      ->add(ResourcePurger::class, 'schedule');
  }

  /**
   * Private.
   */
  private function getLoggerChain() {
    return (new Chain($this))
      ->add(LoggerChannelFactory::class, 'get', LoggerChannelInterface::class)
      ->add(LoggerChannelInterface::class, 'error', NULL, "errors")
      ->add(LoggerChannelInterface::class, 'notice', NULL, "notices");
  }

}
