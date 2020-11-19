<?php

namespace Drupal\Tests\datastore\EventSubscriber;

use Drupal\common\Resource;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\datastore\EventSubscriber\Subscriber;
use Drupal\datastore\Service;
use Drupal\datastore\Service\ResourcePurger;
use Drupal\metastore\Events\DatasetPublication;
use Drupal\metastore\Events\Registration;
use Drupal\Tests\common\Traits\ServiceCheckTrait;
use MockChain\Chain;
use MockChain\Options;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;

/**
 *
 */
class SubscriberTest extends TestCase {
  use ServiceCheckTrait;

  /**
   *
   */
  public function test() {
    $url = 'http://hello.world/file.csv';
    $resource = new Resource($url, 'text/csv');
    $event = new Registration($resource);

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
   * Private.
   */
  private function getContainerChain() {
    $this->checkService('dkan.datastore.service', 'datastore');

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

    $mockDatasetPublication = (new Chain($this))
      ->add(DatasetPublication::class, 'getNode', ContentEntityInterface::class)
      ->getMock();

    $options = (new Options())
      ->add('dkan.datastore.service.resource_purger', ResourcePurger::class)
      ->index(0);

    $containerChain = (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(ResourcePurger::class, 'schedulePurging')
      ->add(ContentEntityInterface::class, 'uuid', 1);

    \Drupal::setContainer($containerChain->getMock());

    $subscriber = new Subscriber();
    $voidReturn = $subscriber->purgeResources($mockDatasetPublication);
    $this->assertNull($voidReturn);
  }

}
