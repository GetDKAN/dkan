<?php

namespace Drupal\Tests\metastore\Unit\Factory;

use Drupal\metastore\Factory\Sae;
use Drupal\metastore\FileSchemaRetriever;
use Drupal\metastore\Storage\AbstractEntityStorage;
use Drupal\metastore\Storage\NodeStorageFactory;
use MockChain\Chain;
use PHPUnit\Framework\TestCase;
use Drupal\metastore\Sae\Sae as Engine;

/**
 *
 */
class SaeTest extends TestCase {

  /**
   *
   */
  public function test() {
    $schemaRetriever = (new Chain($this))
      ->add(FileSchemaRetriever::class, "retrieve", "")
      ->getMock();

    $storage = (new Chain($this))
      ->add(NodeStorageFactory::class, 'getInstance', AbstractEntityStorage::class)
      ->getMock();

    $factory = new Sae($schemaRetriever, $storage);
    $object = $factory->getInstance("dataset");
    $this->assertTrue($object instanceof Engine);
  }

}
