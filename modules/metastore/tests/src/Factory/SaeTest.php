<?php

namespace Drupal\Tests\metastore\Unit\Factory;

use PHPUnit\Framework\TestCase;
use Sae\Sae as Engine;
use Drupal\metastore\Storage\Data;
use Drupal\metastore\Factory\Sae;
use Drupal\metastore\SchemaRetriever;
use MockChain\Chain;

/**
 *
 */
class SaeTest extends TestCase {

  /**
   *
   */
  public function test() {
    $schemaRetriever = (new Chain($this))
      ->add(SchemaRetriever::class, "retrieve", "")
      ->getMock();

    $storage = (new Chain($this))
      ->add(Data::class)
      ->getMock();

    $factory = new Sae($schemaRetriever, $storage);
    $object = $factory->getInstance("dataset");
    $this->assertTrue($object instanceof Engine);
  }

}
