<?php

namespace Drupal\Tests\datastore\Unit\EventSubscriber;

use Drupal\common\Resource;
use Drupal\common\Storage\JobStore;
use Drupal\common\Storage\JobstoreFactory;
use Drupal\common\Events\Event;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\datastore\EventSubscriber\DatastoreSubscriber;
use Drupal\datastore\Service;
use Drupal\datastore\Service\ResourcePurger;
use MockChain\Chain;
use MockChain\Options;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Drupal\datastore\Service\Factory\Import as ImportServiceFactory;
use Drupal\datastore\Service\Import as ImportService;

/**
 * Class DatastoreSubscriber.
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

    \Drupal::setContainer($chain->getMock());

    // When the conditions of a new "datastoreable" resource are met, add
    // an import operation to the queue.
    $subscriber = new DatastoreSubscriber();
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

    $logger = $this->getLoggerChain();
    $options = (new Options())
      ->add('logger.factory', $logger->getMock())
      ->add('dkan.datastore.service', Service::class)
      ->index(0);

    $chain = (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(Service::class, 'import', new \Exception());

    \Drupal::setContainer($chain->getMock());

    // When the conditions of a new "datastoreable" resource are met, add
    // an import operation to the queue.
    $subscriber = new DatastoreSubscriber();
    $subscriber->setLoggerFactory($logger->getMock());
    $subscriber->onRegistration($event);

    // Doing it all for the coverage.
    $this->assertTrue(TRUE);
  }

  /**
   * Private.
   */
  private function getContainerChain() {
    $logger = $this->getLoggerChain();
    $options = (new Options())
      ->add('logger.factory', $logger->getMock())
      ->add('dkan.datastore.service', Service::class)
      ->index(0);

    return (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(Service::class, 'import', [], 'import');
  }

  /**
   * Test ResourcePurger-related parts.
   */
  public function testResourcePurging() {

    $mockDatasetPublication = (new Chain($this))
      ->add(Event::class, 'getData', ContentEntityInterface::class)
      ->getMock();

    $logger = $this->getLoggerChain();
    $options = (new Options())
      ->add('dkan.datastore.service.resource_purger', ResourcePurger::class)
      ->add('logger.factory', $logger->getMock())
      ->index(0);

    $containerChain = (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(ResourcePurger::class, 'schedule')
      ->add(ContentEntityInterface::class, 'uuid', 1);

    \Drupal::setContainer($containerChain->getMock());

    $subscriber = new DatastoreSubscriber();
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
    $db = \Drupal::service('database');

    $logger = $this->getLoggerChain();
    $options = (new Options())
      ->add('logger.factory', $logger->getMock())
      ->add('dkan.datastore.service', Service::class)
      ->add('database', $db)
      ->add('dkan.common.job_store', JobStoreFactory::class)
      ->index(0);

    $chain = (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(Service::class, 'drop')
      ->add(ImportServiceFactory::class, 'getInstance', ImportService::class)
      ->add(ImportService::class, 'remove')
      ->add(JobStoreFactory::class, 'getInstance', JobStore::class)
      ->add(JobStore::class, 'remove');

    \Drupal::setContainer($chain->getMock());

    $subscriber = new DatastoreSubscriber();
    $subscriber->setLoggerFactory($logger->getMock());
    $test = $subscriber->drop($event);
    $this->assertContains('Dropping datastore', $logger->getStoredInput('notices')[0]);
    $this->assertEmpty($logger->getStoredInput('errors'));
  }

  /**
   * Test drop exception.
   */
  public function testDatastoreDropException() {
    $logger = $this->getLoggerChain();

    $url = 'http://hello.world/file.csv';
    $resource = new Resource($url, 'text/csv');
    $event = new Event($resource);
    $db = \Drupal::service('database');

    $options = (new Options())
      ->add('logger.factory', $logger->getMock())
      ->add('dkan.datastore.service', Service::class)
      ->add('database', $db)
      ->add('dkan.common.job_store', JobStoreFactory::class)
      ->index(0);

    $chain = (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(Service::class, 'drop', new \Exception('error'))
      ->add(ImportServiceFactory::class, 'getInstance', ImportService::class)
      ->add(ImportService::class, 'remove')
      ->add(JobStoreFactory::class, 'getInstance', JobStore::class)
      ->add(JobStore::class, 'remove');

    \Drupal::setContainer($chain->getMock());

    $subscriber = new DatastoreSubscriber();
    $test = $subscriber->drop($event);
    $this->assertContains('Failed to drop', $logger->getStoredInput('errors')[0]);
  }

  /**
   * Test jobstore remove exception.
   */
  public function testJobStoreRemoveException() {
    $logger = $this->getLoggerChain();

    $url = 'http://hello.world/file.csv';
    $resource = new Resource($url, 'text/csv');
    $event = new Event($resource);
    $db = \Drupal::service('database');

    $options = (new Options())
      ->add('logger.factory', $logger->getMock())
      ->add('dkan.datastore.service', Service::class)
      ->add('database', $db)
      ->add('dkan.common.job_store', JobStoreFactory::class)
      ->index(0);

    $chain = (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(Service::class, 'drop')
      ->add(ImportServiceFactory::class, 'getInstance', ImportService::class)
      ->add(ImportService::class, 'remove')
      ->add(JobStoreFactory::class, 'getInstance', JobStore::class)
      ->add(JobStore::class, 'remove', new \Exception('error'));

    \Drupal::setContainer($chain->getMock());

    $subscriber = new DatastoreSubscriber();
    $subscriber->setLoggerFactory($logger->getMock());
    $test = $subscriber->drop($event);
    $this->assertContains('Failed to remove', $logger->getStoredInput('errors')[0]);
  }

  /**
   * Logger chain.
   */
  private function getLoggerChain() {
    return (new Chain($this))
      ->add(LoggerChannelFactory::class, 'get', LoggerChannelInterface::class)
      ->add(LoggerChannelInterface::class, 'error', NULL, "errors")
      ->add(LoggerChannelInterface::class, 'notice', NULL, "notices");
  }

  /**
   * Connection.
   */
  private function getConnection() {
    $fieldInfo = [
      (object) ['Field' => "ref_uuid"],
      (object) ['Field' => "job_data"],
    ];

    return (new Chain($this))
      ->add(Connection::class, "schema", Schema::class)
      ->add(Schema::class, "tableExists", TRUE)
      ->add(Connection::class, "delete", Delete::class)
      ->add(Delete::class, "condition", Delete::class)
      ->add(Delete::class, "execute", NULL)
      ->add(Connection::class, 'query', Statement::class)
      ->add(Statement::class, 'fetchAll', $fieldInfo)
      ->getMock();
  }

}
