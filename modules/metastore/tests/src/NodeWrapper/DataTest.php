<?php

namespace Drupal\Tests\metastore\NodeWrapper;

use Drupal\Core\Entity\EntityInterface;
use Drupal\metastore\NodeWrapper\Data;
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

    $entity = (new Chain($this))
      ->add(EntityInterface::class)
      ->getMock();

    new Data($entity);
  }

  /**
   *
   */
  public function testNonDataNode() {
    $this->expectExceptionMessage("We only work with data nodes.");

    $node = (new Chain($this))
      ->add(Node::class, "bundle", "blah")
      ->getMock();

    new Data($node);
  }

}
