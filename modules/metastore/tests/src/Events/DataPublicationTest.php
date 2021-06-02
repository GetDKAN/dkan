<?php

namespace Drupal\Tests\metastore\Unit\Events;

use MockChain\Chain;
use PHPUnit\Framework\TestCase;
use Drupal\common\Events\Event;
use Drupal\metastore\MetastoreItemInterface;

class DataPublicationTest extends TestCase {

  public function test() {
    $mockNode = (new Chain($this))
      ->add(MetastoreItemInterface::class)
      ->getMock();

    $datasetPublication = new Event($mockNode);

    $this->assertEquals($mockNode, $datasetPublication->getData());
  }

}
