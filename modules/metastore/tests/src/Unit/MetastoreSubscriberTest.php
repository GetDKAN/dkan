<?php

namespace Drupal\Tests\metastore\Unit\EventSubscriber;

use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\StreamWrapper\PublicStream;
use Drupal\Core\StreamWrapper\StreamWrapperManager;

use Drupal\common\DataResource;
use Drupal\common\Events\Event;
use Drupal\common\Storage\JobStore;
use Drupal\metastore\EventSubscriber\MetastoreSubscriber;
use Drupal\metastore\MetastoreService;
use Drupal\metastore\ResourceMapper;
use Drupal\Tests\metastore\Unit\MetastoreServiceTest;

use MockChain\Chain;
use MockChain\Options;
use PHPUnit\Framework\TestCase;
use RootedData\RootedJsonData;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Unit tests for the `MetastoreSubscriber` class.
 *
 * @see \Drupal\metastore\EventSubscriber\MetastoreSubscriber
 */
class MetastoreSubscriberTest extends TestCase {

  /**
   * Host protocol and domain for testing file path and download URL.
   *
   * @var string
   */
  const HOST = 'http://h-o.st';

  /**
   * The ValidMetadataFactory class used for testing.
   *
   * @var \Drupal\metastore\ValidMetadataFactory|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $validMetadataFactory;

  /**
   * @inheritDoc
   */
  protected function setUp(): void {
    parent::setUp();
    $this->initializeContainerWithRequestStack();
    $this->validMetadataFactory = MetastoreServiceTest::getValidMetadataFactory($this);
  }

  /**
   * Initialize `\Drupal::$container` with a custom 'request_stack' service.
   */
  protected function initializeContainerWithRequestStack(): void {
    $options = (new Options())
      ->add('request_stack', RequestStack::class)
      ->index(0);
    $container = (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(RequestStack::class, 'getCurrentRequest', new class { public function getHost() { return MetastoreSubscriberTest::HOST; } })
      ->getMock();
    \Drupal::setContainer($container);
  }

  /**
   * Create a JSON Metadata distribution with the given file path.
   *
   * @param string $file_path
   *   Optional resource file path and download URL.
   */
  protected function createDistribution(string $file_path = ''): RootedJsonData {
    $file_path = $file_path ?: (self::HOST . '/' . uniqid() . '.csv');
    return $this->validMetadataFactory->get(json_encode([
      'identifier' => uniqid(),
      'data' => [
        '@type' => 'dcat:Distribution',
        'title' => 'Test Distribution',
        'format' => 'csv',
        'downloadURL' => $file_path,
        '%Ref:downloadURL' => [
          [
            'data' => [
              'identifier' => uniqid(),
              'filePath' => $file_path,
              'mimeType' => 'text/csv',
              'perspective' => 'source',
              'version' => '1',
            ],
          ],
        ],
      ],
    ]), 'distribution');
  }

  /**
   * Tests that if a resource is not in use elsewhere, it is removed.
   */
  public function testCleanupWithResourceNotInUseElsewhere(): void {
    $resource_path = self::HOST . '/single.csv';
    // Create a test resource.
    $resource = new DataResource($resource_path, 'text/csv');
    // Create a test distribution.
    $distribution = $this->createDistribution($resource_path);

    // Construct and set `\Drupal::container` mock.
    $options = (new Options())
      ->add('request_stack', RequestStack::class)
      ->add('stream_wrapper_manager', StreamWrapperManager::class)
      ->index(0);
    $container_chain = (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(StreamWrapperManager::class, 'getViaUri', PublicStream::class)
      ->add(PublicStream::class, 'getExternalUrl', self::HOST);

    \Drupal::setContainer($container_chain->getMock());

    // Initialize successful removal exception message.
    $removal_message = 'Removed Successfully!';
    // Construct new MetastoreSubscriber dependency chain.
    $options = (new Options())
      ->add('logger.factory', LoggerChannelFactory::class)
      ->add('dkan.metastore.service', MetastoreService::class)
      ->add('dkan.metastore.resource_mapper', ResourceMapper::class)
      ->add('database', Connection::class)
      ->index(0);
    $chain = (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(MetastoreService::class, 'get', $distribution)
      ->add(MetastoreService::class, 'getAll', [$distribution])
      ->add(ResourceMapper::class, 'get', $resource)
      ->add(ResourceMapper::class, 'remove', new \Exception($removal_message))
      ->add(LoggerChannelFactory::class, 'get', LoggerChannelInterface::class)
      ->add(LoggerChannelInterface::class, 'error', NULL, 'errors');

    // Ensure the `ResourceMapper::remove()` method was reached by checking for
    // the successful removal exception message.
    $this->expectException(\Exception::class);
    $this->expectExceptionMessage($removal_message);
    // Test `MetastoreSubscriber::cleanResourceMapperTable()`.
    $subscriber = MetastoreSubscriber::create($chain->getMock());
    $subscriber->cleanResourceMapperTable(new Event($distribution->{'$.identifier'}));
  }

  /**
   * Tests that if a resource is in use elsewhere, it is not removed.
   *
   * @doesNotPerformAssertions
   */
  public function testCleanupWithResourceInUseElsewhere(): void {
    $resource_path = self::HOST . '/single.csv';
    // Create a test resource.
    $resource = new DataResource($resource_path, 'text/csv');
    // Create a test distribution.
    $distribution_1 = $this->createDistribution($resource_path);
    $distribution_2 = $this->createDistribution($resource_path);

    // Initialize `\Drupal::container`.
    $options = (new Options())
      ->add('stream_wrapper_manager', StreamWrapperManager::class)
      ->index(0);
    $container_chain = (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(PublicStream::class, 'getExternalUrl', self::HOST)
      ->add(StreamWrapperManager::class, 'getViaUri', PublicStream::class);
    \Drupal::setContainer($container_chain->getMock());

    // Intialize container for constructing `MetastoreSubscriber` service.
    $options = (new Options())
      ->add('logger.factory', LoggerChannelFactory::class)
      ->add('dkan.metastore.service', MetastoreService::class)
      ->add('dkan.metastore.resource_mapper', ResourceMapper::class)
      ->add('database', Connection::class)
      ->index(0);

    $chain = (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(MetastoreService::class, 'get', $distribution_1)
      ->add(MetastoreService::class, 'getAll', [$distribution_1, $distribution_2])
      ->add(ResourceMapper::class, 'get', $resource)
      ->add(ResourceMapper::class, 'remove', new \LogicException('Erroneous attempt to remove resource which is in use elsewhere'))
      ->add(LoggerChannelFactory::class, 'get', LoggerChannelInterface::class)
      ->add(LoggerChannelInterface::class, 'error', NULL, 'errors');

    $subscriber = MetastoreSubscriber::create($chain->getMock());
    $subscriber->cleanResourceMapperTable(new Event($distribution_1->{'$.identifier'}));
  }
}
