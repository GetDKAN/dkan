<?php

namespace Drupal\Tests\metastore\Unit\Factory;

use PHPUnit\Framework\TestCase;
use Sae\Sae as Engine;
use Drupal\data_content_type\Storage\Data;
use Drupal\metastore\Factory\Sae;
use Drupal\schema\SchemaRetriever;
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
