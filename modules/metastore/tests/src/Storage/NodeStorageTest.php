<?php

namespace Drupal\Tests\metastore\Storage;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\metastore\Storage\NodeStorage as MetastoreNodeStorage;
use Drupal\node\NodeStorage;
use MockChain\Chain;
use PHPUnit\Framework\TestCase;

/**
 * Class DataTest
 *
 * @package Drupal\Tests\metastore\Storage
 */
class NodeStorageTest extends TestCase {

  public function testGetStorageNode() {

    $etm = (new Chain($this))
      ->add(EntityTypeManager::class, 'getStorage', NodeStorage::class)
      ->add(NodeStorage::class, 'getEntityType', EntityTypeInterface::class)
      ->getMock();

    $data = new MetastoreNodeStorage('dataset', $etm);
    $this->assertInstanceOf(NodeStorage::class, $data->getEntityStorage());
    $this->assertEquals('field_json_metadata', $data->getMetadataField());
  }
}
