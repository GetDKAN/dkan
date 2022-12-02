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
    $jobStore = $this->getJobstore();
    $config = [
      'temporaryDirectory' => '/tmp',
      'filePath' => __DIR__ . '/../../../files/tiny.csv',
    ];
    $fetcher = new FileFetcherJob('abc', $jobStore, $config);
    $fetcher->run();

    $state = $fetcher->getState();
    $this->assertEquals(
      file_get_contents($state['source']),
      file_get_contents($state['destination'])
    );
  }

  /**
   * Test bad path.
   */
  public function testCopyMissingFile() {
    $jobStore = $this->getJobstore();
    $config = [
      'temporaryDirectory' => '/tmp',
      'filePath' => __DIR__ . '/../../../files/missing.csv',
    ];
    $fetcher = new FileFetcherJob('abc', $jobStore, $config);
    $fetcher->run();

    $result = $fetcher->getResult();
    $this->assertStringContainsString("Error opening file", $result->getError());
  }

  /**
   * Test bad url.
   */
  public function testCopyBadUrl() {
    $jobStore = $this->getJobstore();
    $config = [
      'temporaryDirectory' => '/tmp',
      'filePath' => __DIR__ . 'http://something.fakeurl',
    ];
    $fetcher = new FileFetcherJob('abc', $jobStore, $config);
    $fetcher->run();

    $result = $fetcher->getResult();
    $this->assertStringContainsString("Error opening file", $result->getError());
  }

  /**
   * Test bad destination.
   */
  public function testBadDestinationPath() {
    $jobStore = $this->getJobstore();
    $config = [
      'temporaryDirectory' => '/badTempDir',
      'filePath' => __DIR__ . '/../../../files/tiny.csv',
    ];
    $fetcher = new FileFetcherJob('abc', $jobStore, $config);
    $fetcher->run();

    $result = $fetcher->getResult();
    $this->assertStringContainsString("No such file or directory", $result->getError());
  }

  /**
   * Test empty config.
   */
  public function testInvalidConfig() {
    $jobStore = $this->getJobstore();
    $this->expectExceptionMessage("Constructor missing expected config");
    (new FileFetcherJob('abc', $jobStore, []));
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
