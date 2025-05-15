<?php

namespace Drupal\Tests\metastore\Unit\NodeWrapper;

use Drupal\Core\Entity\EntityFieldManager;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityRepository;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Entity\RevisionableStorageInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\metastore\NodeWrapper\Data;
use Drupal\metastore\NodeWrapper\NodeDataFactory;
use Drupal\node\Entity\Node;
use MockChain\Chain;
use MockChain\Options;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;

/**
 * @coversDefaultClass \Drupal\metastore\NodeWrapper\Data
 * @covers \Drupal\metastore\NodeWrapper\Data
 *
 * @group dkan
 * @group metastore
 * @group unit
 */
class DataTest extends TestCase {

  public function testGetLatestRevisionGetUsAWrapper() {
    $node = (new Chain($this))
      ->add(Node::class, 'bundle', 'data')
      ->addd('__isset', true)
      ->addd('__get', Node::class)
      ->addd('isNew', false)
      ->addd('id', 123)
      ->addd('getLoadedRevisionId', 111)
      ->getMock();

    $entityTypeManager = (new Chain($this))
      ->add(EntityTypeManager::class, 'getStorage', RevisionableStorageInterface::class)
      ->add(EntityTypeManager::class, 'findDefinitions', ['node'])
      ->add(RevisionableStorageInterface::class, 'getLatestRevisionId', 123)
      ->addd('loadRevision', $node)
      ->getMock();

    $container = (new Chain($this))
      ->add(Container::class, 'get', (new Options())
        ->add('entity_type.manager', $entityTypeManager)
        ->index(0)
      )
      ->add(EntityTypeManager::class, 'getStorage', RevisionableStorageInterface::class)
      ->getMock();

    \Drupal::setContainer($container);

    $wrapper = new Data($node, $entityTypeManager);
    $this->assertTrue(
      $wrapper->getLatestRevision() instanceof Data
    );
  }

  public function testGetLatestRevisionGiveUsNull() {
    $node = (new Chain($this))
      ->add(Node::class, 'bundle', 'data')
      ->addd('__isset', true)
      ->addd('__get', Node::class)
      ->addd('isNew', true)
      ->getMock();

    $entityTypeManager = (new Chain($this))
      ->add(EntityTypeManager::class, 'getStorage', RevisionableStorageInterface::class)
      ->getMock();

    $container = (new Chain($this))
      ->add(Container::class)
      ->getMock();

    \Drupal::setContainer($container);

    $wrapper = new Data($node, $entityTypeManager);
    $this->assertNull(
      $wrapper->getLatestRevision()
    );
  }

  public function testGetPublishedRevisionGetUsAWrapper() {
    $node = (new Chain($this))
      ->add(Node::class, 'bundle', 'data')
      ->addd('__isset', true)
      ->addd('__get', Node::class)
      ->addd('isNew', false)
      ->addd('id', 123)
      ->addd('isPublished', true)
      ->getMock();

    $entityTypeManager = (new Chain($this))
      ->add(EntityTypeManager::class, 'getStorage', RevisionableStorageInterface::class)
      ->add(EntityTypeManager::class, 'findDefinitions', ['node'])
      ->add(RevisionableStorageInterface::class, 'load', $node)
      ->getMock();

    $container = (new Chain($this))
      ->add(Container::class, 'get', (new Options())
        ->add('entity_type.manager', $entityTypeManager)
        ->index(0)
      )
      ->add(EntityTypeManager::class, 'getStorage', RevisionableStorageInterface::class)
      ->getMock();

    \Drupal::setContainer($container);

    $wrapper = new Data($node, $entityTypeManager);
    $this->assertTrue(
      $wrapper->getPublishedRevision() instanceof Data
    );
  }

  public function testGetPublishedRevisionGiveUsNull() {
    $node = (new Chain($this))
      ->add(Node::class, 'bundle', 'data')
      ->addd('__isset', true)
      ->addd('__get', Node::class)
      ->addd('isNew', true)
      ->getMock();

    $entityTypeManager = (new Chain($this))
      ->add(EntityTypeManager::class, 'getStorage', RevisionableStorageInterface::class)
      ->getMock();

    $container = (new Chain($this))
      ->add(Container::class)
      ->getMock();

    \Drupal::setContainer($container);

    $wrapper = new Data($node, $entityTypeManager);
    $this->assertNull(
      $wrapper->getPublishedRevision()
    );
  }

  /**
   *
   */
  public function testNotNode() {

    $entityRepository = (new Chain($this))
      ->add(EntityRepository::class, 'loadEntityByUuid', EntityInterface::class)
      ->getMock();

    $entityTypeManager = (new Chain($this))
      ->add(EntityTypeManager::class, 'getStorage', RevisionableStorageInterface::class)
      ->getMock();

    $factory = new NodeDataFactory($entityRepository, $entityTypeManager);
    $this->expectExceptionMessage('Entity must be a node of bundle data.');
    $factory->getInstance("123");
  }

  /**
   *
   */
  public function testNotDataNode() {
    $entityRepository = (new Chain($this))
      ->add(EntityRepository::class, 'loadEntityByUuid', Node::class)
      ->add(Node::class, 'bundle', 'blah')
      ->getMock();

    $entityTypeManager = (new Chain($this))
      ->add(EntityTypeManager::class, 'getStorage', RevisionableStorageInterface::class)
      ->getMock();

    $factory = new NodeDataFactory($entityRepository, $entityTypeManager);
    $this->expectExceptionMessage('Entity must be a node of bundle data.');
    $factory->getInstance("123");
  }

  /**
   *
   */
  public function testDataNodeWrap() {
    $entityRepository = (new Chain($this))
      ->add(EntityRepository::class, 'loadEntityByUuid', Node::class)
      ->getMock();

    $entity = (new Chain($this))
      ->add(Node::class, 'bundle', 'data')
      ->add(Node::class, 'uuid', '123')
      ->add(Node::class, 'get', FieldItemListInterface::class)
      ->add(FieldItemListInterface::class, 'getString', '')
      ->add(Node::class, 'set', TRUE)
      ->getMock();

    $container_chain = (new Chain($this))
      ->add(\Drupal\Component\DependencyInjection\Container::class, 'get', EntityFieldManager::class)
      ->add(EntityFieldManager::class, 'getFieldDefinitions', []);

    $container = $container_chain->getMock();
    \Drupal::setContainer($container);

    $entityTypeManager = (new Chain($this))
      ->add(EntityTypeManager::class, 'getStorage', RevisionableStorageInterface::class)
      ->getMock();

    $factory = new NodeDataFactory($entityRepository, $entityTypeManager);
    $data = $factory->wrap($entity);
    $this->assertEquals('123', $data->getIdentifier());
  }

  public function testDataNodeAdditionalMethods() {
    $entityRepository = (new Chain($this))
      ->add(EntityRepository::class, 'loadEntityByUuid', Node::class)
      ->getMock();

    $entityTypeManager = (new Chain($this))
      ->add(EntityTypeManager::class, 'getStorage', RevisionableStorageInterface::class)
      ->getMock();

    $factory = new NodeDataFactory($entityRepository, $entityTypeManager);
    $this->assertEquals('node', $factory->getEntityType());
    $this->assertEquals(['data'], $factory->getBundles());
    $this->assertEquals('field_json_metadata', $factory->getMetadataField());
  }
}
