<?php

namespace Drupal\Tests\metastore\Unit\Reference;

use Drupal\Core\Cache\CacheTagsInvalidator;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\Queue\QueueInterface;
use Drupal\metastore\NodeWrapper\NodeDataFactory;
use Drupal\metastore\Reference\OrphanChecker;
use Drupal\metastore\Reference\ReferenceLookup;
use Drupal\metastore\Storage\Data;
use Drupal\metastore\Storage\DataFactory;
use Drupal\metastore\Storage\NodeData;
use MockChain\Chain;
use MockChain\Options;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @todo Finish these tests.
 */
class OrphanCheckerTest extends TestCase {

  // /**
  //  *
  //  */
  // public function testGetReferencers() {
  //   $options = (new Options)
  //     ->add('dkan.metastore.reference_lookup', ReferenceLookup::class)
  //     ->add('dkan.metastore.storage', DataFactory::class)
  //     ->add('dkan.metasotre.metastore_item_factory', NodeDataFactory::class)
  //     ->add('cache_tags.invalidator', CacheTagsInvalidator::class)
  //     ->add('module_handler', ModuleHandler::class);

  //   $container = (new Chain($this))
  //     ->add(ContainerInterface::class, 'get', $options)
  //     ->add(DataFactory::class, 'getInstance', NodeData::class)
  //     ->add(NodeDataFactory::class, 'getInstance', Data::class)
  //     ->getMock();

  //   \Drupal::setContainer($container);
  //   $referenceLookup = \Drupal::service('dkan.metastore.reference_lookup');

  //   $referencers = $referenceLookup->getReferencers('dataset', '123', 'distribution')

  // }

  // /**
  //  *
  //  */
  // public function testUpdate() {
  //   $config = (new Chain($this))
  //     ->add(ConfigFactory::class, 'get', ImmutableConfig::class)
  //     ->add(ImmutableConfig::class, 'get', ['name'])
  //     ->getMock();

  //   $queueChain = (new Chain($this))
  //     ->add(QueueFactory::class, 'get', QueueInterface::class)
  //     ->add(QueueInterface::class, 'createItem', NULL, 'items');
  //   $queue = $queueChain->getMock();

  //   $service = new OrphanChecker($config, $queue);
  //   $service->processReferencesInUpdatedDataset((object) ["name" => "1"], (object) ["name" => "2"]);

  //   $expected = [0 => ['name', 1]];
  //   $this->assertEquals($expected, $queueChain->getStoredInput('items'));
  // }

}
