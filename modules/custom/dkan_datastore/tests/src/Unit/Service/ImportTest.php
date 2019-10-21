<?php

use Drupal\dkan_datastore\Storage\JobStoreFactory;
use PHPUnit\Framework\TestCase;
use Drupal\dkan_datastore\Service\Import as Service;
use Dkan\Datastore\Resource;
use Drupal\dkan_common\Tests\Mock\Chain;
use Drupal\dkan_datastore\Storage\JobStore;
use Drupal\dkan_datastore\Storage\DatabaseTableFactory;
use Drupal\dkan_datastore\Storage\DatabaseTable;
use Procrastinator\Result;
use Dkan\Datastore\Importer;

/**
 *
 */
class ImportTest extends TestCase {

  /**
   *
   */
  public function test() {
    $resource = new Resource("blah", "/");

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
    $service->import();

    $this->assertTrue($service->getResult() instanceof Result);
  }

}
