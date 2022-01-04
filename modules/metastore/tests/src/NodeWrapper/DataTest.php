<?php

namespace Drupal\Tests\metastore\NodeWrapper;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityRepository;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\metastore\Storage\MetastoreNodeStorageFactory;
use Drupal\node\Entity\Node;
use MockChain\Chain;
use PHPUnit\Framework\TestCase;

/**
 *
 */
class DataTest extends TestCase {

  /**
   *
   */
  public function testNotNode() {
    $this->expectExceptionMessage("We only work with nodes.");

    $entityRepository = (new Chain($this))
      ->add(EntityRepository::class, 'loadEntityByUuid', EntityInterface::class)
      ->getMock();

    $factory = new MetastoreNodeStorageFactory($entityRepository);
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

    $factory = new MetastoreNodeStorageFactory($entityRepository);
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

    $factory = new MetastoreNodeStorageFactory($entityRepository);
    $data = $factory->wrap($entity);
    $this->assertEquals('123', $data->getIdentifier());
  }

  public function testDataNodeAdditionalMethods() {
    $entityRepository = (new Chain($this))
      ->add(EntityRepository::class, 'loadEntityByUuid', Node::class)
      ->getMock();

    $factory = new MetastoreNodeStorageFactory($entityRepository);
    $this->assertEquals('node', $factory->getEntityType());
    $this->assertEquals(['data'], $factory->getBundles());
    $this->assertEquals('field_json_metadata', $factory->getMetadataField());
  }

}
