<?php

namespace Drupal\Tests\metastore_entity\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\metastore\MetastoreDataNode;
use Drupal\node\Entity\Node;
use MockChain\Chain;
use PHPUnit\Framework\TestCase;

/**
 *
 */
class MetastoreItemTest extends TestCase {

  /**
   *
   */
  public function testNotNode() {
    $this->expectExceptionMessage("We only work with nodes.");

    $entity = (new Chain($this))
      ->add(EntityInterface::class)
      ->getMock();

    new MetastoreDataNode($entity);
  }

  /**
   *
   */
  public function testNonDataNode() {
    $this->expectExceptionMessage("We only work with data nodes.");

    $node = (new Chain($this))
      ->add(Node::class, "bundle", "blah")
      ->getMock();

    new MetastoreDataNode($node);
  }

}
