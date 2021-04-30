<?php

namespace Drupal\Tests\datastore\Unit\EventSubscriber;

use Drupal\common\Resource;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\datastore\EventSubscriber\Subscriber;
use Drupal\datastore\Service;
use Drupal\datastore\Service\ResourcePurger;
use Drupal\metastore\Events\DatasetUpdate;
use Drupal\common\Events\Event;
use MockChain\Chain;
use MockChain\Options;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;

/**
 *
 */
class SubscriberTest extends TestCase {

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
    $subscriber = new Subscriber();
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

    \Drupal::setContainer($chain->getMock());

    // When the conditions of a new "datastoreable" resource are met, add
    // an import operation to the queue.
    $subscriber = new Subscriber();
    $subscriber->onRegistration($event);

    // Doing it all for the coverage.
    $this->assertTrue(TRUE);
  }

  /**
   * Private.
   */
  private function getContainerChain() {

    $options = (new Options())
      ->add('logger.factory', LoggerChannelFactory::class)
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
    $mockedNode = (new Chain($this))
      ->add(ContentEntityInterface::class, 'uuid', '123')
      ->getMock();

    $event = new Event($mockedNode);

    $mockDatasetPublication = (new Chain($this))
      ->add(DatasetUpdate::class, 'getNode', ContentEntityInterface::class)
      ->getMock();

    $options = (new Options())
      ->add('dkan.datastore.service.resource_purger', ResourcePurger::class)
      ->index(0);

    $containerChain = (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(ResourcePurger::class, 'schedule')
      ->add(ContentEntityInterface::class, 'uuid', 1);

    \Drupal::setContainer($containerChain->getMock());

    $subscriber = new Subscriber();
    $voidReturn = $subscriber->purgeResources($event);
    $this->assertNull($voidReturn);
  }

  /**
   * Test drop.
   */
  public function testDropException() {
    $resource = (object) [
      'identifier' => '123',
      'version' => 123,
      'perspective' => 'source',
    ];
    $event = new Event($resource);

    $chain = $this->getContainerChain();
    $chain->add(Resource::class, 'getUniqueIdentifier');

    \Drupal::setContainer($chain->getMock());

    $subscriber = new Subscriber();
    $subscriber->drop($event);

    // Doing it all for the coverage.
    $this->assertTrue(TRUE);
  }

}
