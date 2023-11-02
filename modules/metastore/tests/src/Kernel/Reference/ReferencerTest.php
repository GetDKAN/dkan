<?php

namespace Drupal\Tests\metastore\Kernel\Reference;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Field\FieldItemListInterface;

use Drupal\common\DataResource;
use Drupal\KernelTests\KernelTestBase;
use Drupal\metastore\Reference\MetastoreUrlGenerator;
use Drupal\metastore\Reference\Referencer;
use Drupal\metastore\Storage\DataFactory;
use Drupal\metastore\Storage\NodeData;
use Drupal\node\Entity\Node;
use Drupal\node\NodeStorage;

use MockChain\Chain;

/**
 * @group dkan
 * @group metastore
 * @group kernel
 */
class ReferencerTest extends KernelTestBase {

  protected static $modules = [
    'common',
    'metastore',
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

  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('resource_mapping');
  }

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
        ->addd('uuid', NULL)
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
      ->add(NodeData::class, 'getEntityIdFromUuid', '1')
      ->add(NodeData::class, 'getEntityLatestRevision', NULL)
      ->add(NodeData::class, 'store', 'abc')
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

  /**
   * Test that CSV format translates to correct mediatype if mediatype not supplied.
   */
  public function testChangeMediaType() {
    $this->markTestIncomplete('this test is still broken.');
    /** @var \Drupal\metastore\ResourceMapper $resource_mapper */
    $resource_mapper = $this->container->get('dkan.metastore.resource_mapper');

    $downloadUrl = 'https://dkan-default-content-files.s3.amazonaws.com/phpunit/district_centerpoints_small.csv';
    $resource = new DataResource($downloadUrl, 'application/octet-stream');

    $this->assertTrue(
      $resource_mapper->register($resource)
    );
    $this->assertInstanceOf(DataResource::class, $resource_mapper->get($resource->getIdentifier()));

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
    /** @var \Drupal\metastore\Reference\Referencer $referencer */
    $referencer = $this->container->get('dkan.metastore.referencer');
    $referencer->reference(json_decode($json));

    $storedResource = DataResource::createFromRecord(json_decode($container_chain->getStoredInput('resource')[0]));
    // A new resource should have been stored, with the mimetype set to text/csv
    $this->assertEquals('text/csv', $storedResource->getMimeType());
  }

}
