<?php

use Drupal\dkan_datastore\Service\ImporterList\ImporterList;
use Drupal\dkan_datastore\Storage\JobStore;
use Drupal\dkan_common\Tests\Mock\Sequence;
use FileFetcher\FileFetcher;
use Drupal\dkan_common\Tests\Mock\Chain;
use Drupal\dkan_common\Tests\Mock\Options;
use PHPUnit\Framework\TestCase;
use Procrastinator\Result;

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
    $sequence->add([$fileFetcher]);
    $sequence->add([]);

    $jobStore = (new Chain($this))
      ->add(JobStore::class, "retrieveAll", $sequence)
      ->getMock();

    $list = ImporterList::getList($jobStore);
    $this->assertTrue(is_array($list));
  }

}
