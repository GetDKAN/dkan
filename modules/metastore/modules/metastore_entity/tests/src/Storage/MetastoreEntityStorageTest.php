<?php

namespace Drupal\Tests\metastore_entity\Storage;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\metastore\Storage\AbstractEntityStorage;
use Drupal\metastore_entity\Storage\MetastoreEntityStorage;
use Drupal\node\NodeStorage;
use MockChain\Chain;
use PHPUnit\Framework\TestCase;

/**
 * Class DataTest
 *
 * @package Drupal\Tests\metastore\Storage
 */
class MetastoreEntityStorageTest extends TestCase {

  public function testGetStorageNode() {

    $etm = (new Chain($this))
      ->add(EntityTypeManager::class, 'getStorage', MetastoreEntityStorage::class)
      ->getMock();

    $data = new MetastoreEntityStorage('dataset', $etm);
    $this->assertInstanceOf(MetastoreEntityStorage::class, $data->getEntityStorage());
  }

}
