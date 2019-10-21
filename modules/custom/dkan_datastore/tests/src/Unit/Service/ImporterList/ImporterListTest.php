<?php

use Dkan\Datastore\Importer;
use Dkan\Datastore\Resource;
use Drupal\dkan_datastore\Service\Factory\Resource as ResourceFactory;
use Drupal\dkan_datastore\Service\ImporterList\ImporterList;
use Drupal\dkan_datastore\Service\Resource as ResourceService;
use Drupal\dkan_datastore\Storage\JobStore;
use Drupal\dkan_common\Tests\Mock\Sequence;
use FileFetcher\FileFetcher;
use Drupal\dkan_common\Tests\Mock\Chain;
use Drupal\dkan_common\Tests\Mock\Options;
use PHPUnit\Framework\TestCase;
use Procrastinator\Result;
use Drupal\dkan_datastore\Storage\JobStoreFactory;
use Drupal\dkan_datastore\Service\Factory\Import as ImportFactory;
use Drupal\dkan_datastore\Service\Import as ImportService;

/**
 *
 */
class ImporterListTest extends TestCase {

  /**
   *
   */
  public function test() {

    $options = new Options();
    $options->add('total_bytes_copied', 20);
    $options->add('total_bytes', 30);
    $options->add("hello", "hello");

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
      ->add(ResourceService::class, "get", new Resource("blah", ""))
      ->getMock();

    $importServiceFactory = (new Chain($this))
      ->add(ImportFactory::class, "getInstance", ImportService::class)
      ->add(ImportService::class, "getImporter", Importer::class)
      ->getMock();

    $list = ImporterList::getList($jobStoreFactory, $resourceServiceFactory, $importServiceFactory);
    $this->assertTrue(is_array($list));
  }

}
