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
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\StreamWrapper\StreamWrapperInterface;
use Drupal\metastore\Plugin\MetastoreReferenceType\ItemReference;
use Drupal\metastore\Plugin\MetastoreReferenceType\ResourceReference;
use Drupal\metastore\Reference\ReferenceMap;
use Drupal\metastore\Reference\Referencer;
use Drupal\metastore\Reference\ReferenceTypeManager;
use Drupal\metastore\ResourceMapper;
use Drupal\metastore\Storage\DataFactory;
use Drupal\metastore\Storage\NodeData;
use Drupal\metastore\Storage\ResourceMapperDatabaseTable;
use Drupal\node\Entity\Node;
use Drupal\node\NodeStorage;
use Drupal\Tests\metastore\Unit\Plugin\MetastoreReferenceType\MockClient;
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

  const MIME_TYPE = 'text/csv';

  /**
   * List referenceable dataset properties.
   *
   * @var string[]
   */
  const REFERENCEABLE_PROPERTY_LIST = [
    'keyword' => 0,
    'theme' => 'theme',
    'distribution' => 'distribution',
    'title' => 0,
    'identifier' => 0,
    'description' => 0,
    'accessLevel' => 0,
    'modified' => 0,
  ];

  protected function setUp(): void {
    // We still have a static method calling \Drupal::service()
    $this->setContainer();
  }

  private function mockReferencer() {
    $definitions = [
      ['id' => 'item', 'class' => ItemReference::class],
      ['id' => 'resource', 'class' => ResourceReference::class],
    ];

    $config = ['schemaId' => 'distribution', 'property' => 'distribution'];
    $itemReference = ItemReference::create($this->getContainer(), $config, 'item', $definitions[0]);

    $config = ['property' => 'downloadURL'];
    $resourceReference = ResourceReference::create($this->getContainer(), $config, 'resource', $definitions[1]);

    $createInstance = (new Options())
      ->add('item', $itemReference)
      ->add('resource', $resourceReference)
      ->index(0);

    $manager = (new Chain($this))
      ->add(ReferenceTypeManager::class, 'getDefinitions', $definitions)
      ->add(ReferenceTypeManager::class, 'createInstance', $createInstance)
      ->getMock();

    $configService = (new Chain($this))
      ->add(ConfigFactoryInterface::class, 'get', ImmutableConfig::class)
      ->add(ImmutableConfig::class, 'get', self::REFERENCEABLE_PROPERTY_LIST)
      ->getMock();

    return new Referencer(new ReferenceMap($manager, $configService));
  }

  private function setContainer() {
    $services = (new Options())
      ->add('stream_wrapper_manager', StreamWrapperManager::class)
      ->add('request_stack', RequestStack::class)
      ->add('datetime.time', Time::class)
      ->index(0);

    $container_chain = (new Chain($this))
      ->add(Container::class, 'get', $services)
      ->add(StreamWrapperManager::class, 'getViaUri', StreamWrapperInterface::class)
      // Fake stream wrapper to simulate local URL.
      ->add(StreamWrapperInterface::class, 'getExternalUrl', 'http://mysite.com')
      ->add(RequestStack::class, 'getCurrentRequest', Request::class)
      ->add(Request::class, 'getHost', 'host');
    \Drupal::setContainer($container_chain->getMock());
  }

  private function getContainer() {
    $options = (new Options())
      ->add('stream_wrapper_manager', StreamWrapperManager::class)
      ->add('logger.factory', LoggerChannelFactory::class)
      ->add('dkan.metastore.storage', DataFactory::class)
      ->add('dkan.metastore.resource_mapper', ResourceMapper::class)
      ->add('file_system', FileSystemInterface::class)
      ->add('entity_type.manager', EntityTypeManager::class)
      ->add('http_client', MockClient::class)
      ->index(0);

    return (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(DataFactory::class, 'getInstance', NodeData::class)
      ->add(NodeData::class, 'retrieveByHash', 'abc')
      ->add(NodeData::class, 'isPublished', TRUE)
      ->add(ResourceMapper::class, 'register', TRUE)
      ->add(ResourceMapper::class, 'filePathExists', TRUE)
      ->getMock();
  }

  /**
   * Test that a new reference is created when needed.
   */
  public function testKeywordDistirbutionReference() {
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
    $this->assertEquals('hello', $data->keyword[0]);
  }

  /**
   * Test that a new reference is created when needed.
   */
  public function testDownloadUrlReference() {
    $referencer = $this->mockReferencer(FALSE);

    $downloadUrl = 'https://dkan-default-content-files.s3.amazonaws.com/phpunit/district_centerpoints_small.csv';
    $json = '
    {
      "title": "blah",
      "downloadURL": "' . $downloadUrl . '",
      "format": "tsv"
    }';
    $data = json_decode($json);
    $referencer->reference($data, 'distribution');
    $identifier = md5($downloadUrl) . '__' . time() . '__' . 'source';
    $this->assertEquals($identifier, $data->downloadURL);
  }

}
