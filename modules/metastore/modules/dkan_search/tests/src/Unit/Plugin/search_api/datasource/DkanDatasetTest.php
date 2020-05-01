<?php

use Drupal\Core\DependencyInjection\Container;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\dkan_data\Storage\Data;
use Drupal\dkan_search\Plugin\search_api\datasource\DkanDataset;
use Drupal\node\NodeInterface;
use MockChain\Chain;
use MockChain\Options;
use MockChain\Sequence;
use PHPUnit\Framework\TestCase;
use Drupal\Core\Entity\EntityTypeRepository;
use Drupal\dkan_search\ComplexData\Dataset;

/**
 *
 */
class DkanDatasetTest extends TestCase {

  /**
   *
   */
  public function test() {
    $containerOptions = (new Options())
      ->add('entity_type.manager', EntityTypeManager::class)
      ->add('entity_type.repository', EntityTypeRepository::class)
      ->add('dkan_data.storage', Data::class)
      ->index(0);

    $nids = [1, 2];
    $executeSequence = (new Sequence())->add(2)->add($nids);
    $container = (new Chain($this))
      ->add(Container::class, 'get', $containerOptions)
      ->add(EntityTypeManager::class, 'getStorage', EntityStorageInterface::class)
      ->add(EntityStorageInterface::class, 'getQuery', QueryInterface::class)
      ->add(EntityStorageInterface::class, 'load', NodeInterface::class)
      ->add(NodeInterface::class, 'uuid', 'xyz')
      ->add(QueryInterface::class, 'condition', QueryInterface::class)
      ->add(QueryInterface::class, 'count', QueryInterface::class)
      ->add(QueryInterface::class, 'execute', $executeSequence)
      ->add(QueryInterface::class, 'range', QueryInterface::class)
      ->add(EntityTypeRepository::class, 'getEntityTypeFromClass', NULL)
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
