<?php

use Drupal\dkan_datastore\Storage\JobStoreFactory;
use PHPUnit\Framework\TestCase;
use Drupal\dkan_datastore\Service;
use Drupal\Component\DependencyInjection\Container;
use Drupal\dkan_common\Tests\Mock\Options;
use Drupal\dkan_common\Tests\Mock\Chain;
use Drupal\dkan_datastore\Service\Factory\Resource as ResourceServiceFactory;
use Drupal\dkan_datastore\Service\Factory\Import as ImportServiceFactory;
use Drupal\Core\Queue\QueueFactory;
use Drupal\dkan_datastore\Service\Resource as ResourceService;
use Drupal\dkan_datastore\Service\Import as ImportService;
use Dkan\Datastore\Resource;
use Procrastinator\Result;
use Drupal\Core\Queue\Memory;

/**
 *
 */
class ServiceTest extends TestCase {

  /**
   *
   */
  public function test() {
    $options = (new Options())
      ->add('dkan_datastore.service.factory.resource', ResourceServiceFactory::class)
      ->add('dkan_datastore.service.factory.import', ImportServiceFactory::class)
      ->add('queue', QueueFactory::class)
      ->add('dkan_datastore.job_store_factory', JobStoreFactory::class);

    $chain = (new Chain($this))
      ->add(Container::class, "get", $options)
      ->add(ResourceServiceFactory::class, "getInstance", ResourceService::class)
      ->add(ResourceService::class, "get", new Resource("1", "file:///hello.txt"))
      ->add(ResourceService::class, "getResult", new Result())
      ->add(ImportServiceFactory::class, "getInstance", ImportService::class)
      ->add(ImportService::class, "import", NULL)
      ->add(ImportService::class, "getResult", new Result())
      ->add(QueueFactory::class, "get", NULL);

    $service = Service::create($chain->getMock());
    $result = $service->import("1");

    $this->assertTrue(is_array($result));
  }

  /**
   *
   */
  public function testDeferred() {
    $options = (new Options())
      ->add('dkan_datastore.service.factory.resource', ResourceServiceFactory::class)
      ->add('dkan_datastore.service.factory.import', ImportServiceFactory::class)
      ->add('queue', QueueFactory::class)
      ->add('dkan_datastore.job_store_factory', JobStoreFactory::class);

    $chain = (new Chain($this))
      ->add(Container::class, "get", $options)
      ->add(ResourceServiceFactory::class, "getInstance", ResourceService::class)
      ->add(ResourceService::class, "get", new Resource("1", "file:///hello.txt"))
      ->add(ImportServiceFactory::class, "getInstance", ImportService::class)
      ->add(QueueFactory::class, "get", Memory::class)
      ->add(Memory::class, "createItem", "123");

    $service = Service::create($chain->getMock());
    $result = $service->import("1", TRUE);

    $this->assertTrue(is_array($result));
  }

}
