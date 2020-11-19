<?php

namespace Drupal\Tests\metastore\Storage;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\metastore\Storage\Data;
use Drupal\node\NodeStorage;
use MockChain\Chain;
use PHPUnit\Framework\TestCase;

/**
 * Class DataTest
 *
 * @package Drupal\Tests\metastore\Storage
 */
class DataTest extends TestCase {

  public function testGetStorageNode() {

    $etm = (new Chain($this))
      ->add(EntityTypeManager::class, 'getStorage', NodeStorage::class)
      ->getMock();

    $data = new Data('dataset', $etm);
    $this->assertInstanceOf(NodeStorage::class, $data->getNodeStorage());
  }

}
