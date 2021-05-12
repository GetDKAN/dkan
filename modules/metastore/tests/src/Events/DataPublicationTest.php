<?php

namespace Drupal\Tests\metastore\Unit\Events;

use MockChain\Chain;
use PHPUnit\Framework\TestCase;
use Drupal\common\Events\Event;

class DataPublicationTest extends TestCase {

  public function test() {
    $mockItem = (new Chain($this))
      ->add(MetastoreItemInterface::class)
      ->getMock();

    $datasetPublication = new Event($mockItem);

    $this->assertEquals($mockItem, $datasetPublication->getData());
  }

}
