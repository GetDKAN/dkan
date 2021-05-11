<?php

namespace Drupal\Tests\metastore\Unit\Events;

use Drupal\common\Events\Event;
use Drupal\node\NodeInterface;
use MockChain\Chain;
use PHPUnit\Framework\TestCase;

class DataPublicationTest extends TestCase {

  public function test() {
    $mockNode = (new Chain($this))
      ->add(NodeInterface::class)
      ->getMock();

    $datasetPublication = new Event($mockNode);

    $this->assertEquals($mockNode, $datasetPublication->getData());
  }

}
