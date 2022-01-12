<?php

namespace Drupal\Tests\metastore_search\Unit\Plugin\search_api\datasource;

use Drupal\Core\DependencyInjection\Container;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\metastore\Storage\Data;
use Drupal\metastore\Storage\DataFactory;
use Drupal\metastore_search\Plugin\search_api\datasource\DkanDataset;
use Drupal\node\NodeInterface;
use MockChain\Chain;
use MockChain\Options;
use PHPUnit\Framework\TestCase;
use Drupal\Core\Entity\EntityTypeRepository;
use Drupal\metastore_search\ComplexData\Dataset;

/**
 * Class DkanDatasetTest.
 *
 * @package Drupal\Tests\metastore_search\Unit\Plugin\search_api\datasource
 * @group metastore_search
 */
class DkanDatasetTest extends TestCase {

  /**
   *
   */
  public function test() {
    $containerOptions = (new Options())
      ->add('entity_type.manager', EntityTypeManager::class)
      ->add('entity_type.repository', EntityTypeRepository::class)
      ->add('dkan.metastore.storage', DataFactory::class)
      ->index(0);

    $nodeMock = (new Chain($this))
      ->add(NodeInterface::class, 'uuid', 'xyz')
      ->getMock();
    $container = (new Chain($this))
      ->add(Container::class, 'get', $containerOptions)
      ->add(EntityTypeManager::class, 'getStorage', EntityStorageInterface::class)
      ->add(EntityStorageInterface::class, 'getQuery', QueryInterface::class)
      ->add(EntityStorageInterface::class, 'loadMultiple', [$nodeMock, $nodeMock])
      ->add(QueryInterface::class, 'accessCheck', QueryInterface::class)
      ->add(QueryInterface::class, 'condition', QueryInterface::class)
      ->add(QueryInterface::class, 'count', QueryInterface::class)
      ->add(QueryInterface::class, 'execute', [1, 2])
      ->add(QueryInterface::class, 'range', QueryInterface::class)
      ->add(EntityTypeRepository::class, 'getEntityTypeFromClass', NULL)
      ->add(DataFactory::class, 'getInstance', Data::class)
      ->add(Data::class, 'retrieve', '{}')
      ->getMock();

    \Drupal::setContainer($container);

    $plugin = new DkanDataset([], 'id', []);
    $ids = $plugin->getItemIds(0);
    $this->assertEquals(json_encode(['xyz', 'xyz']), json_encode($ids));

    $items = $plugin->loadMultiple($ids);
    $this->assertEquals(Dataset::class, get_class($items['xyz']));
  }

}
