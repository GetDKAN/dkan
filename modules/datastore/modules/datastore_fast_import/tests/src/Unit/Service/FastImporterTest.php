<?php

namespace Drupal\Tests\dastastore_fast_import\Unit\Service;

use Drupal\common\Storage\JobStoreFactory;
use Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Drupal\Core\DependencyInjection\Container;
use PHPUnit\Framework\TestCase;
use Drupal\datastore\Service\Import as Service;
use Drupal\common\Resource;
use MockChain\Chain;
use Drupal\common\Storage\JobStore;
use Drupal\datastore\Storage\DatabaseTableFactory;
use Drupal\datastore\Storage\DatabaseTable;
use Procrastinator\Result;
use Dkan\Datastore\Importer;
use Drupal\Core\File\FileSystem;
use Drupal\datastore_fast_import\Service\FastImporter;
use MockChain\Options;

/**
 *
 */
class FastImporterTest extends TestCase {

  /**
   *
   */
  public function testFastImporter() {
    $options = (new Options())
      ->add('file_system', FileSystem::class)
      ->add('event_dispatcher', ContainerAwareEventDispatcher::class)
      ->index(0);

    $filepath = realpath("http://hello.goodby/text.csv");

    $container = (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(FileSystem::class, 'realpath', $filepath)
      ->getMock();

    \Drupal::setContainer($container);

    $resource = new Resource("http://hello.goodby/text.csv", "text/csv");

    $jobStore = (new Chain($this))
      ->add(JobStore::class, "retrieve", "")
      ->add(Importer::class, "run", Result::class)
      ->add(Importer::class, "getResult", Result::class)
      ->add(JobStore::class, "store", "")
      ->getMock();

    $databaseTableFactory = (new Chain($this))
      ->add(DatabaseTableFactory::class, "getInstance", DatabaseTable::class)
      ->getMock();

    $jobStoreFactory = (new Chain($this))
      ->add(JobStoreFactory::class, "getInstance", $jobStore)
      ->getMock();

    $service = new Service($resource, $jobStoreFactory, $databaseTableFactory);
    $service->setImporterClass(FastImporter::class);
    $service->import();

    $this->assertTrue($service->getResult() instanceof Result);
  }

}
