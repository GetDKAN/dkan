<?php

namespace Drupal\Tests\datastore\Plugin\QueueWorker;

use Drupal\Core\DependencyInjection\Container;
use Drupal\datastore\Plugin\QueueWorker\ResourcePurgerWorker;
use Drupal\datastore\Service\ResourcePurger;
use MockChain\Chain;
use MockChain\Options;
use PHPUnit\Framework\TestCase;

class ResourcePurgerWorkerTest extends TestCase {

  public function test() {

    $options = (new Options())
      ->add('dkan.datastore.service.resource_purger', ResourcePurger::class)
      ->index(0);

    $containerChain = (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(ResourcePurger::class, 'schedule', NULL);
    $container = $containerChain->getMock();

    \Drupal::setContainer($container);

    $worker = ResourcePurgerWorker::create($container, [], '', '');
    $voidReturn = $worker->processItem(['uuids' => [], 'allRevisions' => FALSE]);
    $this->assertNull($voidReturn);
  }

}
