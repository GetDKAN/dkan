<?php

namespace Drupal\Tests\metastore\Unit\Events;

use Drupal\metastore\Events\PreReference;
use Drupal\metastore\NodeWrapper\Data;
use MockChain\Chain;
use PHPUnit\Framework\TestCase;

class PreReferenceTest extends TestCase {

  public function test() {
    $mockData = (new Chain($this))
      ->add(Data::class)
      ->getMock();

    $preReference = new PreReference($mockData);

    $this->assertEquals($mockData, $preReference->getData());
  }

}
