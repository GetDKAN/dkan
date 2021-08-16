<?php

namespace Drupal\Tests\datastore\Unit\Plugin\QueueWorker;

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

    $containerMock = (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(ResourcePurger::class, 'purgeMultiple', NULL)
      ->getMock();

    $worker = ResourcePurgerWorker::create($containerMock, [], '', '');
    $voidReturn = $worker->processItem(['uuids' => [], 'prior' => FALSE]);
    $this->assertNull($voidReturn);
  }

}
