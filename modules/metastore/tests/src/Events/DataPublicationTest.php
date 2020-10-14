<?php

namespace Drupal\Tests\metastore\Unit\Events;

use Drupal\metastore\Events\DatasetPublication;
use Drupal\node\NodeInterface;
use MockChain\Chain;
use PHPUnit\Framework\TestCase;

class DataPublicationTest extends TestCase {

  public function test() {
    $mockNode = (new Chain($this))
      ->add(NodeInterface::class)
      ->getMock();

    $datasetPublication = new DatasetPublication($mockNode);

    $this->assertEquals($mockNode, $datasetPublication->getNode());
  }

}
