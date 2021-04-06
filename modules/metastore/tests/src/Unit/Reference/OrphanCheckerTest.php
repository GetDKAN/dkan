<?php

namespace Drupal\Tests\metastore\Unit\Reference;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\Queue\QueueInterface;
use Drupal\metastore\Reference\OrphanChecker;
use MockChain\Chain;
use PHPUnit\Framework\TestCase;

/**
 *
 */
class OrphanCheckerTest extends TestCase {

  /**
   *
   */
  public function testDeleted() {
    $config = (new Chain($this))
      ->add(ConfigFactory::class, 'get', ImmutableConfig::class)
      ->add(ImmutableConfig::class, 'get', ['name'])
      ->getMock();

    $queueChain = (new Chain($this))
      ->add(QueueFactory::class, 'get', QueueInterface::class)
      ->add(QueueInterface::class, 'createItem', NULL, 'items');
    $queue = $queueChain->getMock();

    $service = new OrphanChecker($config, $queue);
    $service->processReferencesInDeletedDataset((object) ["name" => "1"]);

    $expected = [0 => ['name', 1]];
    $this->assertEquals($expected, $queueChain->getStoredInput('items'));
  }

  /**
   *
   */
  public function testUpdate() {
    $config = (new Chain($this))
      ->add(ConfigFactory::class, 'get', ImmutableConfig::class)
      ->add(ImmutableConfig::class, 'get', ['name'])
      ->getMock();

    $queueChain = (new Chain($this))
      ->add(QueueFactory::class, 'get', QueueInterface::class)
      ->add(QueueInterface::class, 'createItem', NULL, 'items');
    $queue = $queueChain->getMock();

    $service = new OrphanChecker($config, $queue);
    $service->processReferencesInUpdatedDataset((object) ["name" => "1"], (object) ["name" => "2"]);

    $expected = [0 => ['name', 1]];
    $this->assertEquals($expected, $queueChain->getStoredInput('items'));
  }

}
