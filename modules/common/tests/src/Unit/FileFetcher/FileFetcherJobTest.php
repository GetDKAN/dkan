<?php

namespace Drupal\Tests\common\Unit\FileFetcher;

use Drupal\common\FileFetcher\FileFetcherJob;
use Drupal\common\Storage\JobStore;
use MockChain\Chain;
use PHPUnit\Framework\TestCase;

/**
 * Test FileFetcherJob class.
 */
class FileFetcherJobTest extends TestCase {

  /**
   * Test local file copy.
   */
  public function testCopyLocalFile() {
    $config = [
      'temporaryDirectory' => '/tmp',
      'filePath' => __DIR__ . '/../../../files/tiny.csv',
    ];
    $jobStore = $this->getJobstore();
    $fetcher = new FileFetcherJob('abc', $jobStore, $config);
    $fetcher->run();

    // [Basic Usage]

    $state = $fetcher->getState();

    $this->assertEquals(
      file_get_contents($state['source']),
      file_get_contents($state['destination'])
    );
  }

  /**
   * Get a mock JobStore object.
   */
  protected function getJobstore() {
    $chain = (new Chain($this))
      ->add(JobStore::class, "setTable", TRUE)
      ->add(JobStore::class, "store", "foo");

    return $chain->getMock();
  }

}
