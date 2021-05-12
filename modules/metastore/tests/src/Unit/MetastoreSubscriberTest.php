<?php

namespace Drupal\Tests\metastore\Unit\EventSubscriber;

use Drupal\common\Resource;
use Drupal\common\Events\Event;
use MockChain\Chain;
use MockChain\Options;
use PHPUnit\Framework\TestCase;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\Logger\LoggerChannelInterface;
use Symfony\Component\DependencyInjection\Container;
use Drupal\metastore\EventSubscriber\MetastoreSubscriber;
use Drupal\metastore\Service;
use Drupal\metastore\ResourceMapper;

/**
 *
 */
class MetastoreSubscriberTest extends TestCase {

  /**
   * Test clean up.
   */
  public function testResourceMapperCleanUp() {
    $logger = $this->getLoggerChain();

    $url = 'http://hello.world/file.csv';
    $resource = new Resource($url, 'text/csv');
    $dist = '{"data":{"%Ref:downloadURL":[{"data":{"identifier":"qwerty","version":"uiop","perspective":"source"}}]}}';
    $event = new Event($resource);

    $options = (new Options())
      ->add('logger.factory', $logger->getMock())
      ->add('dkan.metastore.service', Service::class)
      ->add('dkan.metastore.resource_mapper', ResourceMapper::class)
      ->index(0);

    $chain = (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(Service::class, 'get', $dist)
      ->add(ResourceMapper::class, 'get', $resource)
      ->add(ResourceMapper::class, 'remove');

    \Drupal::setContainer($chain->getMock());

    $subscriber = new MetastoreSubscriber();
    $test = $subscriber->cleanResourceMapperTable($event);
    $this->assertEmpty($logger->getStoredInput('errors'));
  }

  /**
   * Test exception.
   */
  public function testResourceMapperCleanUpException() {
    $logger = $this->getLoggerChain();

    $url = 'http://hello.world/file.csv';
    $resource = new Resource($url, 'text/csv');
    $dist = '{"data":{"%Ref:downloadURL":[{"data":{"identifier":"qwerty","version":"uiop","perspective":"source"}}]}}';
    $event = new Event($resource);

    $options = (new Options())
      ->add('logger.factory', $logger->getMock())
      ->add('dkan.metastore.service', Service::class)
      ->add('dkan.metastore.resource_mapper', ResourceMapper::class)
      ->index(0);

    $chain = (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(Service::class, 'get', $dist)
      ->add(ResourceMapper::class, 'get', $resource)
      ->add(ResourceMapper::class, 'remove', new \Exception('error'));

    \Drupal::setContainer($chain->getMock());

    $subscriber = new MetastoreSubscriber();
    $test = $subscriber->cleanResourceMapperTable($event);
    $this->assertContains('Failed to remove', $logger->getStoredInput('errors')[0]);
  }

  private function getLoggerChain() {
    return (new Chain($this))
      ->add(LoggerChannelFactory::class, 'get', LoggerChannelInterface::class)
      ->add(LoggerChannelInterface::class, 'error', NULL, "errors")
      ->add(LoggerChannelInterface::class, 'notice', NULL, "notices");
  }

}
