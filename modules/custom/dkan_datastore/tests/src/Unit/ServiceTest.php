<?php

use Drupal\dkan_datastore\Storage\JobStore;
use Drupal\dkan_datastore\Storage\JobStoreFactory;
use Drupal\dkan_datastore\Storage\DatabaseTable;
use PHPUnit\Framework\TestCase;
use Drupal\dkan_datastore\Service;
use Drupal\Component\DependencyInjection\Container;
use MockChain\Options;
use MockChain\Chain;
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

    $chain = (new Chain($this))
      ->add(Container::class, "get", $this->getContainerOptions())
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

    $chain = (new Chain($this))
      ->add(Container::class, "get", $this->getContainerOptions())
      ->add(ResourceServiceFactory::class, "getInstance", ResourceService::class)
      ->add(ResourceService::class, "get", new Resource("1", "file:///hello.txt"))
      ->add(ImportServiceFactory::class, "getInstance", ImportService::class)
      ->add(QueueFactory::class, "get", Memory::class)
      ->add(Memory::class, "createItem", "123");

    $service = Service::create($chain->getMock());
    $result = $service->import("1", TRUE);

    $this->assertTrue(is_array($result));
  }

  /**
   *
   */
  public function testDrop() {
    $container = (new Chain($this))
      ->add(Container::class, "get", $this->getContainerOptions())
      ->add(QueueFactory::class, "get", NULL)
      ->add(ResourceServiceFactory::class, "getInstance", ResourceService::class)
      ->add(ResourceService::class, "get", new Resource("1", "file:///hello.txt"))
      ->add(ImportServiceFactory::class, "getInstance", ImportService::class)
      ->add(ImportService::class, "getStorage", DatabaseTable::class)
      ->add(DatabaseTable::class, "destroy", NULL)
      ->add(JobStoreFactory::class, "getInstance", JobStore::class)
      ->add(JobStore::class, "remove", NULL)
      ->getMock();

    $service = Service::create($container);
    $service->drop("1");

    $this->assertTrue(TRUE);
  }

  /**
   *
   */
  private function getContainerOptions() {
    return (new Options())
      ->add('dkan_datastore.service.factory.resource', ResourceServiceFactory::class)
      ->add('dkan_datastore.service.factory.import', ImportServiceFactory::class)
      ->add('queue', QueueFactory::class)
      ->add('dkan_datastore.job_store_factory', JobStoreFactory::class)
      ->index(0);

  }

}
