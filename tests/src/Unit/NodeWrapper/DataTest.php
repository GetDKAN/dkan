<?php

namespace Drupal\Tests\metastore\Unit\NodeWrapper;

use Drupal\Core\Entity\EntityFieldManager;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityRepository;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\metastore\NodeWrapper\Data;
use Drupal\metastore\NodeWrapper\NodeDataFactory;
use Drupal\node\Entity\Node;
use MockChain\Chain;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;

/**
 * Testing the NodeWrapper.
 */
class DataTest extends TestCase
{
  public function testGetOriginalGetUsAWrapper() {
    $node = (new Chain($this))
      ->add(Node::class, 'bundle', 'data')
      ->addd('__isset', true)
      ->addd('__get', Node::class)
      ->getMock();

    $container = (new Chain($this))
      ->add(Container::class)
      ->getMock();

    \Drupal::setContainer($container);

    $wrapper = new Data($node);
    $this->assertTrue(
      $wrapper->getOriginal() instanceof Data
    );
  }

  public function testGetOriginalGiveUsNull() {
    $node = (new Chain($this))
      ->add(Node::class, 'bundle', 'data')
      ->addd('__isset', false)
      ->getMock();

    $container = (new Chain($this))
      ->add(Container::class)
      ->getMock();

    \Drupal::setContainer($container);

    $wrapper = new Data($node);
    $this->assertNull(
      $wrapper->getOriginal()
    );
  }

  /**
   *
   */
  public function testNotNode() {
    $this->expectExceptionMessage("We only work with nodes.");

    $entityRepository = (new Chain($this))
      ->add(EntityRepository::class, 'loadEntityByUuid', EntityInterface::class)
      ->getMock();

    $factory = new NodeDataFactory($entityRepository);
    $factory->getInstance("123");
  }

  /**
   *
   */
  public function testNotDataNode() {
    $this->expectExceptionMessage("We only work with data nodes.");

    $entityRepository = (new Chain($this))
      ->add(EntityRepository::class, 'loadEntityByUuid', Node::class)
      ->add(Node::class, 'bundle', 'blah')
      ->getMock();

    $factory = new NodeDataFactory($entityRepository);
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

    $factory = new NodeDataFactory($entityRepository);
    $data = $factory->wrap($entity);
    $this->assertEquals('123', $data->getIdentifier());
  }

  public function testDataNodeAdditionalMethods() {
    $entityRepository = (new Chain($this))
      ->add(EntityRepository::class, 'loadEntityByUuid', Node::class)
      ->getMock();

    $factory = new NodeDataFactory($entityRepository);
    $this->assertEquals('node', $factory->getEntityType());
    $this->assertEquals(['data'], $factory->getBundles());
    $this->assertEquals('field_json_metadata', $factory->getMetadataField());
  }
}
