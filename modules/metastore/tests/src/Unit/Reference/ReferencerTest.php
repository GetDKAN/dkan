<?php

namespace Drupal\Tests\metastore\Unit\Reference;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\File\FileSystem;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\StreamWrapper\PublicStream;
use Drupal\Core\StreamWrapper\StreamWrapperManager;

use Drupal\common\UrlHostTokenResolver;
use Drupal\metastore\Reference\Referencer;
use Drupal\metastore\ResourceMapper;
use Drupal\metastore\Storage\DataFactory;
use Drupal\metastore\Storage\NodeData;

use GuzzleHttp\Exception\RequestException;
use MockChain\Chain;
use MockChain\Options;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ReferencerTest extends TestCase {

  /**
   * HTTP file path for testing download URL.
   *
   * @var string
   */
  const FILE_PATH = 'tmp/mycsv.csv';

  /**
   * HTTP host protocol and domain for testing download URL.
   *
   * @var string
   */
  const HOST = 'http://example.com';

  /**
   * Test file mime type.
   *
   * @var string
   */
  const MIME_TYPE = 'text/csv';

  /**
   * List referenceable dataset properties.
   *
   * @var string[]
   */
  const REFERENCEABLE_PROPERTY_LIST = [
    'theme' => 0,
    'keyword' => 0,
    'publisher' => 0,
    'distribution' => 'distribution',
    'contactPoint' => 0,
    '@type' => 0,
    'title' => 0,
    'identifier' => 0,
    'description' => 0,
    'accessLevel' => 0,
    'accrualPeriodicity' => 0,
    'describedBy' => 0,
    'describedByType' => 0,
    'issued' => 0,
    'license' => 0,
    'modified' => 0,
    'references' => 0,
    'spatial' => 0,
    'temporal' => 0,
    'isPartOf' => 0,
  ];

  /**
   * Create a test dataset using the supplied download URL.
   */
  private function getData(string $downloadUrl, string $mediaType = NULL): object {
    return (object) [
      'title' => 'Test Dataset No Media Type',
      'description' => 'Hi',
      'identifier'=> '12345',
      'accessLevel'=> 'public',
      'modified'=> '06-04-2020',
      'keyword'=> ['hello'],
      'distribution'=> [
        (object) array_filter([
          'title'=> 'blah',
          'mediaType' => $mediaType,
          'downloadURL'=> $downloadUrl,
        ]),
      ],
    ];
  }

  /**
   * Test the `Referencer::hostify()` method.
   */
  public function testHostify(): void {
    // Initialize `\Drupal::container`.
    $options = (new Options())
      ->add('stream_wrapper_manager', StreamWrapperManager::class)
      ->index(0);
    $container_chain = (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(PublicStream::class, 'getExternalUrl', self::HOST)
      ->add(StreamWrapperManager::class, 'getViaUri', PublicStream::class);
    \Drupal::setContainer($container_chain->getMock());
    // Ensure the hostify method is properly resolving the supplied URL.
    $this->assertEquals(
      'http://' . UrlHostTokenResolver::TOKEN . '/' . self::FILE_PATH,
      Referencer::hostify(self::HOST . '/' . self::FILE_PATH));
  }

  /**
   * Test the remote/local file mime type detection logic.
   */
  public function testMimeTypeDetection(): void {
    // Initialize mock node class.
    $node = new class {
      public function uuid() {
        return '0398f054-d712-4e20-ad1e-a03193d6ab33';
      }
      public function set() {}
      public function save() {}
    };

    // Create a mock file storage class.
    $storage = new class {
      public function loadByProperties() {
        return [
          new class {
            public function getMimeType() { return ReferencerTest::MIME_TYPE; }
          }
        ];
      }
    };

    // Initialize `\Drupal::container`.
    $options = (new Options())
      ->add('dkan.metastore.resource_mapper', ResourceMapper::class)
      ->add('entity_type.manager', EntityTypeManager::class)
      ->add('file_system', FileSystem::class)
      ->add('request_stack', RequestStack::class)
      ->add('stream_wrapper_manager', StreamWrapperManager::class)
      ->add('logger.factory', LoggerChannelFactory::class)
      ->index(0);
    $container_chain = (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(EntityTypeManager::class, 'getStorage', $storage)
      ->add(PublicStream::class, 'getExternalUrl', self::HOST)
      ->add(ResourceMapper::class, 'register', TRUE, 'resource')
      ->add(StreamWrapperManager::class, 'getViaUri', PublicStream::class);
    \Drupal::setContainer($container_chain->getMock());

    // Initialize mock referencer service.
    $entity = (new Chain($this))
      ->add(EntityStorageInterface::class, 'loadByProperties', [$node])
      ->getMock();
    $configService = (new Chain($this))
      ->add(ConfigFactoryInterface::class, 'get', new class { public function get() { return ReferencerTest::REFERENCEABLE_PROPERTY_LIST; } })
      ->getMock();
    $storageFactory = (new Chain($this))
      ->add(DataFactory::class, 'getInstance', NodeData::class)
      ->add(NodeData::class, 'getEntityStorage', $entity)
      ->getMock();
    $referencer = new Referencer($configService, $storageFactory);

    // Test Mime Type detection using the resource `mediaType` property.
    $data = $this->getData(self::HOST . '/' . self::FILE_PATH, self::MIME_TYPE);
    $referencer->reference($data);
    $this->assertEquals(self::MIME_TYPE, $container_chain->getStoredInput('resource')[0]->getMimeType(), 'Unable to fetch MIME type from `mediaType` property');
    // Test Mime Type detection on a local file.
    $data = $this->getData(self::HOST . '/' . self::FILE_PATH);
    $referencer->reference($data);
    $this->assertEquals(self::MIME_TYPE, $container_chain->getStoredInput('resource')[0]->getMimeType(), 'Unable to fetch MIME type for local file');
    // Test Mime Type detection on a remote file.
    $data = $this->getData('https://dkan-default-content-files.s3.amazonaws.com/phpunit/district_centerpoints_small.csv');
    $referencer->reference($data);
    $this->assertEquals(self::MIME_TYPE, $container_chain->getStoredInput('resource')[0]->getMimeType(), 'Unable to fetch MIME type for remote file');
    // Test Mime Type detection on a invalid remote file path.
    $data = $this->getData('http://invalid');
    $this->expectException(RequestException::class);
    $referencer->reference($data);
  }

}
