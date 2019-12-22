<?php

namespace Drupal\Tests\dkan_metastore\Unit\Factory;

use PHPUnit\Framework\TestCase;
use Sae\Sae as Engine;
use Drupal\dkan_data\Storage\Data;
use Drupal\dkan_metastore\Factory\Sae;
use Drupal\dkan_schema\SchemaRetriever;
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
      ->add(Data::class, "blah", NULL)
      ->getMock();

    $factory = new Sae($schemaRetriever, $storage);
    $object = $factory->getInstance("dataset");
    $this->assertTrue($object instanceof Engine);
  }

}
