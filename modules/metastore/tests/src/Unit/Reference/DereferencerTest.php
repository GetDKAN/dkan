<?php

namespace Drupal\Tests\metastore\Unit\Reference;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\DependencyInjection\Container;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\Queue\QueueFactory;
use Drupal\metastore\Exception\MissingObjectException;
use Drupal\metastore\Plugin\MetastoreReferenceType\ItemReference;
use Drupal\metastore\Plugin\MetastoreReferenceType\ResourceReference;
use Drupal\metastore\Reference\Dereferencer;
use Drupal\metastore\Reference\ReferenceMap;
use Drupal\metastore\Reference\ReferenceTypeManager;
use Drupal\metastore\ResourceMapper;
use Drupal\metastore\Service\Uuid5;
use Drupal\metastore\Storage\DataFactory;
use Drupal\metastore\Storage\NodeData;
use Drupal\Tests\metastore\Unit\Plugin\MetastoreReferenceType\MockClient;
use MockChain\Chain;
use MockChain\Options;
use MockChain\ReturnNull;
use MockChain\Sequence;
use PHPUnit\Framework\TestCase;

/**
 *
 */
class DereferencerTest extends TestCase {

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

  /**
   *
   */
  private function mockDereferencer($config, $value) {
    $definitions = [
      ['id' => 'item', 'class' => ItemReference::class],
      ['id' => 'resource', 'class' => ResourceReference::class],
    ];

    $refs = [
      'keyword' => 'keyword',
      'publisher' => 'publisher',
      'title' => NULL,
    ];

    $itemReference = ItemReference::create($this->getContainer($value), $config, 'item', $definitions[0]);

    $config = ['property' => 'downloadURL'];
    $resourceReference = ResourceReference::create($this->getContainer($value), $config, 'resource', $definitions[1]);

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
      ->add(ImmutableConfig::class, 'get', $refs)
      ->getMock();

    return new Dereferencer(new ReferenceMap($manager, $configService));
  }

  private function getContainer($value) {
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
      ->add(NodeData::class, 'isPublished', TRUE)
      ->add(NodeData::class, 'retrieve', $value)
      ->add(ResourceMapper::class, 'register', TRUE)
      ->add(ResourceMapper::class, 'filePathExists', TRUE)
      ->getMock();
  }

  public function testDereferenceBasic() {
    $config = ['schemaId' => 'publisher', 'property' => 'publisher'];
    $uuidService = new Uuid5();
    $uuid = $uuidService->generate('dataset', "some value");
    $value = '{"data":{"name":"Gerardo","company":"CivicActions"}}';

    $valueReferencer = $this->mockDereferencer($config, $value);
    $referenced = $valueReferencer->dereference((object) ['publisher' => $uuid]);

    $this->assertTrue(is_object($referenced));
    $this->assertEquals((object) [
      'name' => 'Gerardo',
      'company' => 'CivicActions',
    ], $referenced->publisher);
  }

  public function testDereferenceDeletedReference() {
    $config = ['schemaId' => 'publisher', 'property' => 'publisher'];
    $value = NULL;
    $uuidService = new Uuid5();
    $uuid = $uuidService->generate('dataset', "some value");

    $valueReferencer = $this->mockDereferencer($config, $value);
    $referenced = $valueReferencer->dereference((object) ['publisher' => $uuid]);

    $this->assertEmpty((array) $referenced);
  }

  /**
   *
   */
  public function testDereferenceMultiple() {
    $config = ['schemaId' => 'keyword', 'property' => 'keyword'];
    $value = (new Sequence())
      ->add('{"data":"Gerardo"}')
      ->add('{"data":"CivicActions"}');
    $valueReferencer = $this->mockDereferencer($config, $value);
    $referenced = $valueReferencer->dereference((object) ['keyword' => ['123456789', '987654321']]);

    $this->assertTrue(is_object($referenced));
    $this->assertEquals("Gerardo", $referenced->keyword[0]);
    $this->assertEquals("CivicActions", $referenced->keyword[1]);
  }

}
