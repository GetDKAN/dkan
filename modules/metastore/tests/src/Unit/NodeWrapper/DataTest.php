<?php

namespace Drupal\Tests\metastore\Unit\NodeWrapper;

use Drupal\metastore\NodeWrapper\Data;
use Drupal\node\Entity\Node;
use MockChain\Chain;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;

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
}
