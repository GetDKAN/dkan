<?php

namespace Drupal\Tests\metastore\Kernel\Reference;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\File\FileSystem;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\StreamWrapper\PublicStream;
use Drupal\Core\StreamWrapper\StreamWrapperManager;

use Drupal\common\DataResource;
use Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Drupal\KernelTests\KernelTestBase;
use Drupal\metastore\DataDictionary\DataDictionaryDiscovery;
use Drupal\metastore\Exception\MissingObjectException;
use Drupal\metastore\Reference\MetastoreUrlGenerator;
use Drupal\metastore\Reference\Referencer;
use Drupal\metastore\ResourceMapper;
use Drupal\metastore\MetastoreService;
use Drupal\metastore\Storage\DataFactory;
use Drupal\metastore\Storage\NodeData;
use Drupal\metastore\Storage\ResourceMapperDatabaseTable;
use Drupal\node\Entity\Node;
use Drupal\node\NodeStorage;

use GuzzleHttp\Exception\ConnectException;
use MockChain\Chain;
use MockChain\Options;
use PHPUnit\Framework\TestCase;
use RootedData\RootedJsonData;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @group dkan
 * @group metastore
 * @group kernel
 *
 * @see Drupal\Tests\metastore\Unit\Reference\ReferencerTest
 */
class ReferencerTest extends KernelTestBase {

  protected static $modules = [
    'common',
    'datastore',
    'metastore',
    'node',
    'user',
  ];

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

    $referencer = new Referencer($configService, $storageFactory, $urlGenerator);
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

  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('node');
    $this->installConfig(['metastore']);
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

  public function provideData() {
    return [
    // Test Mime Type detection using the resource `mediaType` property.
    'mediaType' => [$this->getData(self::HOST . '/' . self::FILE_PATH, self::MIME_TYPE)],
//    $referencer->reference($data);
//    $this->assertEquals(self::MIME_TYPE, $container_chain->getStoredInput('resource')[0]->getMimeType(), 'Unable to fetch MIME type from `mediaType` property');
    // Test Mime Type detection on a local file.
    'local file' => [$this->getData(self::HOST . '/' . self::FILE_PATH)],
//    $referencer->reference($data);
//    $this->assertEquals(self::MIME_TYPE, $container_chain->getStoredInput('resource')[0]->getMimeType(), 'Unable to fetch MIME type for local file');
    // Test Mime Type detection on a remote file.
    'remote file' => [$this->getData('https://dkan-default-content-files.s3.amazonaws.com/phpunit/district_centerpoints_small.csv')],
//    $referencer->reference($data);
//    $this->assertEquals(self::MIME_TYPE, $container_chain->getStoredInput('resource')[0]->getMimeType(), 'Unable to fetch MIME type for remote file');
    // Test Mime Type detection on a invalid remote file path.
    'bad remote file' => [$this->getData('http://invalid')],
//    $this->expectException(ConnectException::class);
    ];
  }

  /**
   * Test the remote/local file mime type detection logic.
   *
   * @dataProvider provideData
   */
  public function testMimeTypeDetection(object $data): void {
    /** @var Referencer $referencer */
    $referencer = $this->container->get('dkan.metastore.referencer');

    $referenced = json_decode($referencer->reference($data));
//    $this->assertEquals(self::MIME_TYPE, $container_chain->getStoredInput('resource')[0]->getMimeType(), 'Unable to fetch MIME type for remote file');

    $this->assertIsObject($referenced);
  }

  public function provideDataDictionaryData() {
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
