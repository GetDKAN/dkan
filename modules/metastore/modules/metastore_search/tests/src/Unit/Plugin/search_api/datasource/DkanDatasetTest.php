<?php

namespace Drupal\Tests\metastore_search\Unit\Plugin\search_api\datasource;

use _PHPStan_7d6f0f6a4\Psr\Container\ContainerInterface;
use Drupal\Core\DependencyInjection\Container;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\TypedData\ComplexDataInterface;
use Drupal\metastore\Exception\MissingObjectException;
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
 * @coversDefaultClass \Drupal\metastore_search\Plugin\search_api\datasource\DkanDataset
 *
 * @package Drupal\Tests\metastore_search\Unit\Plugin\search_api\datasource
 * @group metastore_search
 * @group dkan-core
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
      ->add('string_translation', TranslationInterface::class)
      ->index(0);

    $nodeMock = (new Chain($this))
      ->add(NodeInterface::class, 'uuid', 'xyz')
      ->getMock();
    /** @var \Drupal\Component\DependencyInjection\ContainerInterface $container */
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

    // Satisfy EntityBase.
    \Drupal::setContainer($container);

    $plugin = DkanDataset::create($container, [], 'id', []);

    $ids = $plugin->getItemIds(0);
    $this->assertEquals(json_encode(['xyz', 'xyz']), json_encode($ids));

    $items = $plugin->loadMultiple($ids);
    $this->assertEquals(Dataset::class, get_class($items['xyz']));
  }

  /**
   * @covers ::loadMultiple
   */
  public function testLoadMultipleUnpublished() {
    // The retrieve() method will throw MissingObjectException, so we won't see
    // published nodes at all.
    $data_storage = $this->getMockBuilder(Data::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['retrieve'])
      ->getMockForAbstractClass();
    $data_storage->expects($this->atLeastOnce())
      ->method('retrieve')
      ->will($this->throwException(new MissingObjectException()));

    $metastore_storage = $this->getMockBuilder(DataFactory::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['getInstance'])
      ->getMock();
    $metastore_storage->method('getInstance')
      ->willReturn($data_storage);

    $dkan_dataset = $this->getMockBuilder(DkanDataset::class)
      ->disableOriginalConstructor()
      ->onlyMethods([])
      ->getMock();

    $ref_storage = new \ReflectionProperty($dkan_dataset, 'metastoreStorageService');
    $ref_storage->setAccessible(TRUE);
    $ref_storage->setValue($dkan_dataset, $metastore_storage);

    // Any set of IDs should result in 0 loaded nodes.
    $some_ids = ['an_id', 'another_id'];
    $this->assertCount(0, $dkan_dataset->loadMultiple($some_ids));
  }

  /**
   * @covers ::getItemId
   */
  public function testGetItemId() {
    $dkan_dataset = $this->getMockBuilder(DkanDataset::class)
      ->disableOriginalConstructor()
      // Don't mock any methods.
      ->onlyMethods([])
      ->getMock();

    $item = $this->getMockBuilder(ComplexDataInterface::class)
      ->onlyMethods(['get'])
      ->getMockForAbstractClass();
    $item->method('get')
      ->with('identifier')
      ->willReturn('test_identifier');

    $this->assertEquals('test_identifier', $dkan_dataset->getItemId($item));
  }

}
