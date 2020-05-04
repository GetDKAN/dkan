<?php

use Drupal\datastore\Storage\JobStoreFactory;
use PHPUnit\Framework\TestCase;
use Drupal\datastore\Service\Import as Service;
use Dkan\Datastore\Resource;
use MockChain\Chain;
use Drupal\datastore\Storage\JobStore;
use Drupal\datastore\Storage\DatabaseTableFactory;
use Drupal\datastore\Storage\DatabaseTable;
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
    $resource = new Resource("blah", "/", "text/csv");

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
