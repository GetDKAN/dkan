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

    $url = 'http://hello.world/file.csv';
    $resource = new Resource($url, 'text/csv');
    $dist = '{"data":{"%Ref:downloadURL":[{"data":{"identifier":"qwerty","version":"uiop","perspective":"source"}}]}}';
    $event = new Event($resource);

    $logger = $this->getLoggerChain();

    $metastoreService = (new Chain($this))
      ->add(Service::class, 'get', $dist)
      ->getMock();

    $resourceMapper = (new Chain($this))
      ->add(ResourceMapper::class, 'get', $resource)
      ->add(ResourceMapper::class, 'remove')
      ->getMock();

    $subscriber = new MetastoreSubscriber(
      $logger->getMock(),
      $metastoreService,
      $resourceMapper
    );
    $test = $subscriber->cleanResourceMapperTable($event);
    $this->assertEmpty($logger->getStoredInput('errors'));
  }

  /**
   * Test exception.
   */
  public function testResourceMapperCleanUpException() {

    $url = 'http://hello.world/file.csv';
    $resource = new Resource($url, 'text/csv');
    $dist = '{"data":{"%Ref:downloadURL":[{"data":{"identifier":"qwerty","version":"uiop","perspective":"source"}}]}}';
    $event = new Event($resource);

    $logger = $this->getLoggerChain();

    $metastoreService = (new Chain($this))
      ->add(Service::class, 'get', $dist)
      ->getMock();

    $resourceMapper = (new Chain($this))
      ->add(ResourceMapper::class, 'get', $resource)
      ->add(ResourceMapper::class, 'remove', new \Exception('error'))
      ->getMock();

    $subscriber = new MetastoreSubscriber(
      $logger->getMock(),
      $metastoreService,
      $resourceMapper
    );
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
