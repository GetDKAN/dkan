<?php

namespace Drupal\Tests\metastore\Unit\Reference;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\File\FileSystem;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\metastore\Reference\Referencer;
use Drupal\metastore\ResourceMapper;
use Drupal\metastore\Storage\DataFactory;
use Drupal\metastore\Storage\NodeData;
use Drupal\node\NodeStorage;
use MockChain\Chain;
use MockChain\Options;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ReferencerTest extends TestCase {

  public function testHostify() {
    $container = (new Chain($this))
      ->add(Container::class, 'get', RequestStack::class)
      ->add(RequestStack::class, 'getCurrentRequest', Request::class)
      ->add(Request::class, 'getHost', 'test.test')
      ->getMock();

    \Drupal::setContainer($container);

    $this->assertEquals(
      'http://h-o.st/mycsv.txt',
      Referencer::hostify("http://test.test/mycsv.txt"));
  }

  private function mockReferencer($existing = TRUE) {
    if ($existing) {
      $node = new class {
        public function uuid() {
          return '0398f054-d712-4e20-ad1e-a03193d6ab33';
        }
        public function set() {}
        public function save() {}
      };
    }
    else {
      $node = new class {
        public function uuid() {
          return NULL;
        }
        public function set() {}
        public function save() {}
        public function setRevisionLogMessage() {}
      };
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
      ->add(ImmutableConfig::class, 'get', $this->getPropertyList())
      ->getMock();

    $configService = (new Chain($this))
      ->add(ConfigFactoryInterface::class, 'get', $immutableConfig)
      ->getMock();

    $referencer = new Referencer($configService, $storageFactory);
    return $referencer;
  }

  private function getContainer() {
    $options = (new Options())
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
   *
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
   * Test that TSV format translates to correct mediatype if mediatype not supplied
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
   * Get list of properties.
   */
  private function getPropertyList() {
    return [
      'keyword' => 0,
      'distribution' => 'distribution',
      'title' => 0,
      'identifier' => 0,
      'description' => 0,
      'accessLevel' => 0,
      'modified' => 0,
    ];
  }
}
