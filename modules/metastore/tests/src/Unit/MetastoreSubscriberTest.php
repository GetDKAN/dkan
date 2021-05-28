<?php

namespace Drupal\Tests\metastore\Unit\EventSubscriber;

use Drupal\common\Resource;
use Drupal\common\Events\Event;
use Drupal\common\Storage\JobStore;
use MockChain\Chain;
use MockChain\Options;
use PHPUnit\Framework\TestCase;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\Logger\LoggerChannelInterface;
use Symfony\Component\DependencyInjection\Container;
use Drupal\metastore\EventSubscriber\MetastoreSubscriber;
use Drupal\metastore\Service;
use Drupal\metastore\ResourceMapper;
use Drupal\Tests\metastore\Unit\ServiceTest;

/**
 *
 */
class MetastoreSubscriberTest extends TestCase {

  /**
   * The ValidMetadataFactory class used for testing.
   *
   * @var \Drupal\metastore\ValidMetadataFactory|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $validMetadataFactory;

  protected function setUp(): void {
    parent::setUp();
    $this->validMetadataFactory = ServiceTest::getValidMetadataFactory($this);
  }

  /**
   * Test clean up.
   */
  public function testResourceMapperCleanUp() {

    $url = 'http://hello.world/file.csv';
    $resource = new Resource($url, 'text/csv');
    $dist = '{"data":{"%Ref:downloadURL":[{"data":{"identifier":"qwerty","version":"uiop","perspective":"source"}}]}}';
    $dist = $this->validMetadataFactory->get('distribution', $dist);
    $event = new Event($resource);

    $options = (new Options())
      ->add('logger.factory', LoggerChannelFactory::class)
      ->add('dkan.metastore.service', Service::class)
      ->add('dkan.metastore.resource_mapper', ResourceMapper::class)
      ->add("database", Connection::class)
      ->index(0);

    $chain = (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(Service::class, 'get', $dist)
      ->add(ResourceMapper::class, 'get', $resource)
      ->add(ResourceMapper::class, 'remove')
      ->add(LoggerChannelFactory::class, 'get', LoggerChannelInterface::class)
      ->add(LoggerChannelInterface::class, 'error', NULL, 'errors');

    $subscriber = MetastoreSubscriber::create($chain->getMock());
    $test = $subscriber->cleanResourceMapperTable($event);
    $this->assertEmpty($chain->getStoredInput('errors'));
  }

  /**
   * Test exception.
   */
  public function testResourceMapperCleanUpException() {

    $url = 'http://hello.world/file.csv';
    $resource = new Resource($url, 'text/csv');
    $dist = '{"data":{"%Ref:downloadURL":[{"data":{"identifier":"qwerty","version":"uiop","perspective":"source"}}]}}';
    $dist = $this->validMetadataFactory->get('distribution', $dist);
    $event = new Event($resource);

    $options = (new Options())
      ->add('logger.factory', LoggerChannelFactory::class)
      ->add('dkan.metastore.service', Service::class)
      ->add('dkan.metastore.resource_mapper', ResourceMapper::class)
      ->add("database", Connection::class)
      ->index(0);

    $chain = (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(Service::class, 'get', $dist)
      ->add(ResourceMapper::class, 'get', $resource)
      ->add(ResourceMapper::class, 'remove', new \Exception('error'))
      ->add(LoggerChannelFactory::class, 'get', LoggerChannelInterface::class)
      ->add(LoggerChannelInterface::class, 'error', NULL, 'errors');

    $subscriber = MetastoreSubscriber::create($chain->getMock());
    $test = $subscriber->cleanResourceMapperTable($event);
    $this->assertContains('Failed to remove', $chain->getStoredInput('errors')[0]);

  }

}
