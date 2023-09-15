<?php

namespace Drupal\Tests\metastore\Unit\Reference;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\File\FileSystem;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\StreamWrapper\PublicStream;
use Drupal\Core\StreamWrapper\StreamWrapperManager;

use Drupal\common\UrlHostTokenResolver;
use Drupal\common\DataResource;
use Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Drupal\metastore\Reference\Referencer;
use Drupal\metastore\ResourceMapper;
use Drupal\metastore\Storage\DataFactory;
use Drupal\metastore\Storage\NodeData;
use Drupal\metastore\Storage\ResourceMapperDatabaseTable;
use Drupal\node\Entity\Node;
use Drupal\node\NodeStorage;

use GuzzleHttp\Exception\ConnectException;
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
  public const FILE_PATH = 'tmp/mycsv.csv';

  /**
   * HTTP host protocol and domain for testing download URL.
   *
   * @var string
   */
  public const HOST = 'http://example.com';

  public const MIME_TYPE = 'text/csv';

  /**
   * List referenceable dataset properties.
   *
   * @var string[]
   */
  public const REFERENCEABLE_PROPERTY_LIST = [
    'keyword' => 0,
    'distribution' => 'distribution',
    'title' => 0,
    'identifier' => 0,
    'description' => 0,
    'accessLevel' => 0,
    'modified' => 0,
  ];

  private function mockReferencer($existing = TRUE) {
    if ($existing) {
      $node = (new Chain($this))
        ->add(Node::class, 'get', FieldItemListInterface::class)
          ->addd('uuid', '0398f054-d712-4e20-ad1e-a03193d6ab33')
        ->add(FieldItemListInterface::class, 'getString', 'orphaned')
        ->add(Node::class, 'set')
        ->add(Node::class, 'save')
        ->getMock();
    }
    else {
      $node = (new Chain($this))
        ->add(Node::class, 'get', FieldItemListInterface::class)
          ->addd('uuid', null)
        ->add(FieldItemListInterface::class, 'getString', 'orphaned')
        ->add(Node::class, 'set')
        ->add(Node::class, 'save')
        ->add(Node::class, 'setRevisionLogMessage')
        ->getMock();
    }

    $storageFactory = (new Chain($this))
      ->add(DataFactory::class, 'getInstance', NodeData::class)
      ->add(NodeData::class, 'getEntityStorage', NodeStorage::class)
      ->add(NodeStorage::class, 'loadByProperties', [$node])
      ->add(NodeData::class, 'getEntityIdFromUuid', "1")
      ->add(NodeData::class, 'getEntityLatestRevision', NULL)
      ->add(NodeData::class, 'store', "abc")
      ->getMock();

    $immutableConfig = (new Chain($this))
      ->add(ImmutableConfig::class, 'get', self::REFERENCEABLE_PROPERTY_LIST)
      ->getMock();

    $configService = (new Chain($this))
      ->add(ConfigFactoryInterface::class, 'get', $immutableConfig)
      ->getMock();

    $referencer = new Referencer($configService, $storageFactory);
    return $referencer;
  }

  private function getContainer() {
    $options = (new Options())
      ->add('stream_wrapper_manager', StreamWrapperManager::class)
      ->add('logger.factory', LoggerChannelFactory::class)
      ->add('request_stack', RequestStack::class)
      ->add('dkan.metastore.resource_mapper', ResourceMapper::class)
      ->add('file_system', FileSystem::class)
      ->index(0);

    $container_chain = (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(RequestStack::class, 'getCurrentRequest', Request::class)
      ->add(Request::class, 'getHost', 'test.test')
      ->add(ResourceMapper::class, 'register', TRUE, 'resource')
      ->add(FileSystem::class, 'getTempDirectory', '/tmp');

    return $container_chain;
  }

  /**
   * Test file mime type.
   *
   * @var string
   */
  public function testNoMediaType() {
    $container_chain = $this->getContainer();
    $container = $container_chain->getMock();
    \Drupal::setContainer($container);
    $referencer = $this->mockReferencer();

    $downloadUrl = 'https://dkan-default-content-files.s3.amazonaws.com/phpunit/district_centerpoints_small.csv';
    $json = '
    {
      "title": "Test Dataset No Media Type",
      "description": "Hi",
      "identifier": "12345",
      "accessLevel": "public",
      "modified": "06-04-2020",
      "keyword": ["hello"],
        "distribution": [
          {
            "title": "blah",
            "downloadURL": "' . $downloadUrl . '"
          }
        ]
    }';
    $data = json_decode($json);
    $referencer->reference($data);
    $this->assertEquals('text/csv', $container_chain->getStoredInput('resource')[0]->getMimeType());
  }

  /**
   * Test that CSV format translates to correct mediatype if mediatype not supplied
   */
  public function testWithMediaTypeConflictingFormat() {
    $container_chain = $this->getContainer();
    $container = $container_chain->getMock();
    \Drupal::setContainer($container);
    $referencer = $this->mockReferencer();

    $downloadUrl = 'https://dkan-default-content-files.s3.amazonaws.com/phpunit/district_centerpoints_small.csv';
    $json = '
    {
      "title": "Test Dataset No Media Type",
      "description": "Hi",
      "identifier": "12345",
      "accessLevel": "public",
      "modified": "06-04-2020",
      "keyword": ["hello"],
        "distribution": [
          {
            "title": "blah",
            "downloadURL": "' . $downloadUrl . '",
            "format": "csv",
            "mediaType": "text/tab-separated-values"
          }
        ]
    }';
    $data = json_decode($json);
    $referencer->reference($data);
    $this->assertEquals('text/tab-separated-values', $container_chain->getStoredInput('resource')[0]->getMimeType());
  }

  /**
   * Test that CSV format translates to correct mediatype if mediatype not supplied
   */
  public function testNoMediaTypeWitCsvFormat() {
    $container_chain = $this->getContainer();
    $container = $container_chain->getMock();
    \Drupal::setContainer($container);
    $referencer = $this->mockReferencer();

    $downloadUrl = 'https://dkan-default-content-files.s3.amazonaws.com/phpunit/district_centerpoints_small.csv';
    $json = '
    {
      "title": "Test Dataset No Media Type",
      "description": "Hi",
      "identifier": "12345",
      "accessLevel": "public",
      "modified": "06-04-2020",
      "keyword": ["hello"],
        "distribution": [
          {
            "title": "blah",
            "downloadURL": "' . $downloadUrl . '",
            "format": "csv"
          }
        ]
    }';
    $data = json_decode($json);
    $referencer->reference($data);
    $this->assertEquals('text/csv', $container_chain->getStoredInput('resource')[0]->getMimeType());
  }

  /**
   * Test that CSV format translates to correct mediatype if mediatype not supplied
   */
  public function testChangeMediaType() {
    $options = (new Options())
      ->add('stream_wrapper_manager', StreamWrapperManager::class)
      ->add('logger.factory', LoggerChannelFactory::class)
      ->add('request_stack', RequestStack::class)
      ->add('dkan.metastore.resource_mapper', ResourceMapper::class)
      ->add('dkan.metastore.resource_mapper_database_table', ResourceMapperDatabaseTable::class)
      ->add('event_dispatcher', ContainerAwareEventDispatcher::class)
      ->add('file_system', FileSystem::class)
      ->index(0);

    $downloadUrl = 'https://dkan-default-content-files.s3.amazonaws.com/phpunit/district_centerpoints_small.csv';
    $resource = new DataResource($downloadUrl, 'application/octet-stream');

    $container_chain = (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(RequestStack::class, 'getCurrentRequest', Request::class)
      ->add(Request::class, 'getHost', 'test.test')
      ->add(ResourceMapper::class, 'getStore', ResourceMapperDatabaseTable::class)
      ->add(ResourceMapper::class, 'validateNewVersion', TRUE)
      ->add(ResourceMapper::class, 'get', $resource)
      ->add(ResourceMapperDatabaseTable::class, 'query', [
        [
          'identifier' => '123',
          'perspective' => DataResource::DEFAULT_SOURCE_PERSPECTIVE,
        ],
      ])
      ->add(ResourceMapperDatabaseTable::class, 'store', '123', 'resource')
      ->add(FileSystem::class, 'getTempDirectory', '/tmp');

    $container = $container_chain->getMock();
    \Drupal::setContainer($container);
    $referencer = $this->mockReferencer();

    $json = '
    {
      "title": "Test Dataset No Format",
      "description": "Hi",
      "identifier": "12345",
      "accessLevel": "public",
      "modified": "06-04-2020",
      "keyword": ["hello"],
        "distribution": [
          {
            "title": "blah",
            "downloadURL": "' . $downloadUrl . '",
            "format": "csv"
          }
        ]
    }';
    $data = json_decode($json);
    $referencer->reference($data);
    $storedResource = DataResource::createFromRecord(json_decode($container_chain->getStoredInput('resource')[0]));
    // A new resource should have been stored, with the mimetype set to text/csv
    $this->assertEquals('text/csv', $storedResource->getMimeType());
  }



  /**
   * Test that TSV format translates to correct mediatype if mediatype not supplied
   */
  public function testNoMediaTypeWithTsvFormat() {
    $container_chain = $this->getContainer();
    $container = $container_chain->getMock();
    \Drupal::setContainer($container);
    $referencer = $this->mockReferencer();

    $downloadUrl = 'https://dkan-default-content-files.s3.amazonaws.com/phpunit/district_centerpoints_small.csv';
    $json = '
    {
      "title": "Test Dataset No Media Type",
      "description": "Hi",
      "identifier": "12345",
      "accessLevel": "public",
      "modified": "06-04-2020",
      "keyword": ["hello"],
        "distribution": [
          {
            "title": "blah",
            "downloadURL": "' . $downloadUrl . '",
            "format": "tsv"
          }
        ]
    }';
    $data = json_decode($json);
    $referencer->reference($data);
    $this->assertEquals('text/tab-separated-values', $container_chain->getStoredInput('resource')[0]->getMimeType());
  }

  /**
   * Test that a new reference is created when needed.
   */
  public function testNewReference() {
    $container_chain = $this->getContainer();
    $container = $container_chain->getMock();
    \Drupal::setContainer($container);
    $referencer = $this->mockReferencer(FALSE);

    $downloadUrl = 'https://dkan-default-content-files.s3.amazonaws.com/phpunit/district_centerpoints_small.csv';
    $json = '
    {
      "title": "Test Dataset No Media Type",
      "description": "Hi",
      "identifier": "12345",
      "accessLevel": "public",
      "modified": "06-04-2020",
      "keyword": ["hello"],
        "distribution": [
          {
            "title": "blah",
            "downloadURL": "' . $downloadUrl . '",
            "format": "tsv"
          }
        ]
    }';
    $data = json_decode($json);
    $referencer->reference($data);
    $this->assertEquals('abc', $data->distribution[0]);
  }

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
    $node = (new Chain($this))
      ->add(Node::class, 'get', FieldItemListInterface::class)
      ->addd('uuid', '0398f054-d712-4e20-ad1e-a03193d6ab33')
      ->add(FieldItemListInterface::class, 'getString', 'orphaned')
      ->add(Node::class, 'set')
      ->add(Node::class, 'save')
      ->getMock();

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
    $this->expectException(ConnectException::class);
    $referencer->reference($data);
  }

}
