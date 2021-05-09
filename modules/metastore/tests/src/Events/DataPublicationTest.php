<?php

namespace Drupal\Tests\metastore\Unit\Events;

use Drupal\metastore\Events\DatasetUpdate;
use Drupal\metastore\NodeWrapper\Data;
use Drupal\node\NodeInterface;
use MockChain\Chain;
use PHPUnit\Framework\TestCase;

class DataPublicationTest extends TestCase {

  public function test() {
    $mockItem = (new Chain($this))
      ->add(MetastoreItemInterface::class)
      ->getMock();

    $datasetPublication = new DatasetUpdate($mockItem);

    $this->assertEquals($mockItem, $datasetPublication->getItem());
  }

}
