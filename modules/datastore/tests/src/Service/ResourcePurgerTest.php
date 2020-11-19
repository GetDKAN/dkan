<?php

namespace Drupal\Tests\datastore\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\DependencyInjection\Container;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\Queue\QueueInterface;
use Drupal\datastore\Service;
use Drupal\datastore\Service\ResourcePurger;
use Drupal\metastore\Storage\Data;
use Drupal\metastore\Storage\DataFactory;
use Drupal\node\NodeInterface;
use Drupal\node\NodeStorageInterface;
use MockChain\Chain;
use MockChain\Options;
use PHPUnit\Framework\TestCase;

/**
 * Class ResourcePurgerTest
 *
 * @package Drupal\Tests\datastore\Service
 */
class ResourcePurgerTest extends TestCase {

  /**
   * Test schedulePurgingAll when neither purge config is set.
   */
  public function testNoPurgeConfig() {

    $chain = $this->getCommonChain()
      ->add(ImmutableConfig::class, 'get', 0)
      ->add(Data::class, 'getNodeStorage', NodeStorageInterface::class)
      ->add(NodeStorageInterface::class, 'getQuery', QueryInterface::class)
      ->add(QueryInterface::class, 'condition', QueryInterface::class)
      ->add(QueryInterface::class, 'execute', []);

    $resourcePurger = ResourcePurger::create($chain->getMock());
    $voidResult = $resourcePurger->schedulePurgingAll(FALSE, TRUE);
    $this->assertNull($voidResult);
  }

  /**
   * Test queueing the purging.
   */
  public function testQueueing() {

    $chain = $this->getCommonChain()
      ->add(ImmutableConfig::class, 'get', 1)
      ->add(Service::class, 'getQueueFactory', QueueFactory::class)
      ->add(QueueFactory::class, 'get', QueueInterface::class)
      ->add(QueueInterface::class, 'createItem', 1);

    $resourcePurger = ResourcePurger::create($chain->getMock());
    $voidResult = $resourcePurger->schedulePurging([], TRUE);
    $this->assertNull($voidResult);
  }

  /**
   * Test figuring out resource purging.
   */
  public function testResourcePurger() {

    $uuids = ['1111-1111'];
    $vids = [];
    $latestRevision = (new Chain($this))
      ->add(NodeInterface::class)
      ->getMock();

    $chain = $this->getCommonChain()
      ->add(ImmutableConfig::class, 'get', 1)
      ->add(Data::class, 'getNodeLatestRevision', $latestRevision)
      ->add(Data::class, 'getNodeStorage', NodeStorageInterface::class)
      ->add(NodeStorageInterface::class, 'revisionIds', $vids);

    $resourcePurger = ResourcePurger::create($chain->getMock());
    $voidResult = $resourcePurger->schedulePurging($uuids, FALSE);
    $this->assertNull($voidResult);
  }

  /**
   * Get common chain.
   */
  private function getCommonChain() {

    $options = (new Options())
      ->add('config.factory', ConfigFactoryInterface::class)
      ->add('dkan.metastore.storage', DataFactory::class)
      ->add('dkan.datastore.service', Service::class)
      ->index(0);

    return (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(ConfigFactoryInterface::class, 'get', ImmutableConfig::class)
      ->add(DataFactory::class, 'getInstance', Data::class);
  }

}
