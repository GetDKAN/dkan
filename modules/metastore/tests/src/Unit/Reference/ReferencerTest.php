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
use Drupal\metastore\DataDictionary\DataDictionaryDiscovery;
use Drupal\metastore\Exception\MissingObjectException;
use Drupal\metastore\MetastoreService;
use Drupal\metastore\Reference\MetastoreUrlGenerator;
use Drupal\metastore\Reference\Referencer;
use Drupal\metastore\ResourceMapper;
use Drupal\metastore\Storage\DataFactory;
use Drupal\metastore\Storage\NodeData;
use Drupal\node\Entity\Node;
use Drupal\node\NodeStorage;
use GuzzleHttp\Client;
use MockChain\Chain;
use MockChain\Options;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RootedData\RootedJsonData;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Mime\MimeTypeGuesserInterface;

/**
 * @covers \Drupal\metastore\Reference\Referencer
 * @coversDefaultClass \Drupal\metastore\Reference\Referencer
 *
 * @group dkan
 * @group metastore
 * @group unit
 */
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

    $urlGenerator = (new Chain($this))
      ->add(MetastoreUrlGenerator::class, 'uriFromUrl', 'dkan://metastore/schemas/data-dictionary/items/111')
      ->getMock();

    $mimeTypeGuesser = (new Chain($this))
      ->add(MimeTypeGuesserInterface::class, 'guessMimeType', self::MIME_TYPE)
      ->getMock();

    return new Referencer(
      $configService,
      $storageFactory,
      $urlGenerator,
      new Client(),
      $mimeTypeGuesser,
      $this->createStub(LoggerInterface::class)
    );
  }

  private function getContainer() {
    $options = (new Options())
      ->add('stream_wrapper_manager', StreamWrapperManager::class)
      ->add('logger.factory', LoggerChannelFactory::class)
      ->add('request_stack', RequestStack::class)
      ->add('dkan.metastore.resource_mapper', ResourceMapper::class)
      ->add('file_system', FileSystem::class)
      ->index(0);

    return (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(RequestStack::class, 'getCurrentRequest', Request::class)
      ->add(Request::class, 'getHost', 'test.test')
      ->add(ResourceMapper::class, 'register', TRUE, 'resource')
      ->add(FileSystem::class, 'getTempDirectory', '/tmp');
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

  public static function formatProvider() {
    return [
      'tsv' => ['tsv', 'text/tab-separated-values'],
      'csv' => ['csv', 'text/csv'],
    ];
  }

  /**
   * Test that format translates to correct mediatype if mediatype not supplied.
   *
   * @dataProvider formatProvider
   */
  public function testNoMediaTypeWithFormat($format, $expected_mime) {
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
            "format": "' . $format . '"
          }
        ]
    }';
    $data = json_decode($json);
    $referencer->reference($data);
    $this->assertEquals($expected_mime, $container_chain->getStoredInput('resource')[0]->getMimeType());
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
   * Test the remote/local file mime type detection logic.
   *
   * @covers ::getLocalMimeType
   * @covers ::getMimeType
   * @covers ::getRemoteMimeType
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

    $urlGenerator = (new Chain($this))
      ->add(MetastoreUrlGenerator::class, 'uriFromUrl', '')
      ->getMock();

    $mimeTypeGuesser = (new Chain($this))
      ->add(MimeTypeGuesserInterface::class, 'guessMimeType', self::MIME_TYPE)
      ->getMock();

    $referencer = new Referencer(
      $configService,
      $storageFactory,
      $urlGenerator,
      new Client(),
      $mimeTypeGuesser,
      $this->createStub(LoggerInterface::class)
    );

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
    // Test Mime Type detection on a invalid remote file path. Defaults to
    // text/plain.
    $data = $this->getData('http://invalid');
    $referencer->reference($data);
    $this->assertEquals(Referencer::DEFAULT_MIME_TYPE, $container_chain->getStoredInput('resource')[0]->getMimeType(), 'Did not use default MIME type for inaccessible remote file.');
  }

  /**
   * @dataProvider provideDataDictionaryData
   */
  public function testDistributionHandlingDataDict($distribution, $describedBy) {
    \Drupal::setContainer($this->getContainer()->getMock());
    $storageFactory = (new Chain($this))
      ->add(DataFactory::class, 'getInstance', NodeData::class)
      ->getMock();

    $configService = (new Chain($this))
      ->add(ConfigFactoryInterface::class, 'get', ImmutableConfig::class)
      ->add(ImmutableConfig::class, 'get', DataDictionaryDiscovery::MODE_REFERENCE)
      ->getMock();

    $urlGenerator = (new Chain($this))
      ->add(MetastoreUrlGenerator::class, 'uriFromUrl', (new Options())
        ->add('http://local-domain.com/api/1/metastore/schemas/data-dictionary/items/111', 'dkan://metastore/schemas/data-dictionary/items/111')
        ->add("http://remote-domain.com/dictionary.pdf", new \DomainException())
        ->add('dkan://metastore/schemas/data-dictionary/items/111', 'dkan://metastore/schemas/data-dictionary/items/111')
        ->add('s3://local-domain.com/api/1/metastore/schemas/data-dictionary/items/111', new \DomainException())
        ->add('dkan://metastore/schemas/data-dictionary/items/222', 'dkan://metastore/schemas/data-dictionary/items/222')
      )
      ->add(MetastoreUrlGenerator::class, 'metastore', MetastoreService::class)
      ->add(MetastoreService::class, 'get', (new Options())
        ->add('111', RootedJsonData::class)
        ->add('222', new MissingObjectException())
      ->index(1)
      )
      ->getMock();

    $http_client = $this->getMockBuilder(Client::class)
      ->disableOriginalConstructor()
      ->getMock();

    $mimeTypeGuesser = (new Chain($this))
      ->add(MimeTypeGuesserInterface::class, 'guessMimeType', self::MIME_TYPE)
      ->getMock();

    $referencer = new Referencer(
      $configService,
      $storageFactory,
      $urlGenerator,
      $http_client,
      $mimeTypeGuesser,
      $this->createStub(LoggerInterface::class)
    );

    if ($describedBy instanceof \Exception) {
      $this->expectException($describedBy::class);
      $this->expectExceptionMessage($describedBy->getMessage());
    }
    $referencer->distributionHandling($distribution);
    $this->assertSame($describedBy, $distribution->describedBy);
  }

  public static function provideDataDictionaryData() {
    return [
      [
        (object) ["describedBy" => "http://local-domain.com/api/1/metastore/schemas/data-dictionary/items/111"],
        "dkan://metastore/schemas/data-dictionary/items/111",
      ],
      [
        (object) ["describedBy" => "http://remote-domain.com/dictionary.pdf"],
        "http://remote-domain.com/dictionary.pdf",
      ],
      [
        (object) ["describedBy" => "dkan://metastore/schemas/data-dictionary/items/111"],
        "dkan://metastore/schemas/data-dictionary/items/111",
      ],
      [
        (object) ["describedBy" => "s3://local-domain.com/api/1/metastore/schemas/data-dictionary/items/111"],
        "s3://local-domain.com/api/1/metastore/schemas/data-dictionary/items/111",
      ],
      [
        (object) ["describedBy" => "dkan://metastore/schemas/data-dictionary/items/222"],
        new \DomainException("is not a valid data-dictionary URI"),
      ],
    ];
  }

}
