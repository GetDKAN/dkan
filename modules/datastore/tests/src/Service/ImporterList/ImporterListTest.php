<?php

use Dkan\Datastore\Importer;
use Dkan\Datastore\Resource;
use Drupal\datastore\Service\Factory\Resource as ResourceFactory;
use Drupal\datastore\Service\ImporterList\ImporterList;
use Drupal\datastore\Service\Resource as ResourceService;
use Drupal\datastore\Storage\JobStore;
use MockChain\Sequence;
use FileFetcher\FileFetcher;
use MockChain\Chain;
use MockChain\Options;
use PHPUnit\Framework\TestCase;
use Procrastinator\Result;
use Drupal\datastore\Storage\JobStoreFactory;
use Drupal\datastore\Service\Factory\Import as ImportFactory;
use Drupal\datastore\Service\Import as ImportService;

/**
 *
 */
class ImporterListTest extends TestCase {

  /**
   *
   */
  public function test() {

    $options = (new Options())
      ->add('total_bytes_copied', 20)
      ->add('total_bytes', 30)
      ->add("hello", "hello")
      ->add("source", "hello")
      ->index(0);

    $fileFetcher = (new Chain($this))
      ->add(FileFetcher::class, "getStateProperty", $options)
      ->add(FileFetcher::class, "getResult", Result::class)
      ->add(Result::class, "getStatus", Result::DONE)
      ->getMock();

    $sequence = new Sequence();
    $sequence->add(["1"]);
    $sequence->add([]);

    $jobStore = (new Chain($this))
      ->add(JobStore::class, "retrieveAll", $sequence)
      ->getMock();

    $jobStoreFactory = (new Chain($this))
      ->add(JobStoreFactory::class, "getInstance", $jobStore)
      ->getMock();

    $resourceServiceFactory = (new Chain($this))
      ->add(ResourceFactory::class, "getInstance", ResourceService::class)
      ->add(ResourceService::class, "getFileFetcher", $fileFetcher)
      ->add(ResourceService::class, "get", new Resource("blah", "", "text/csv"))
      ->getMock();

    $importServiceFactory = (new Chain($this))
      ->add(ImportFactory::class, "getInstance", ImportService::class)
      ->add(ImportService::class, "getImporter", Importer::class)
      ->getMock();

    $list = ImporterList::getList($jobStoreFactory, $resourceServiceFactory, $importServiceFactory);
    $this->assertTrue(is_array($list));
  }

}
