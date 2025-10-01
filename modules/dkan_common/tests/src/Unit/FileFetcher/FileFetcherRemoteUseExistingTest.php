<?php

namespace Drupal\Tests\common\Unit\FileFetcher;

use Drupal\common\FileFetcher\FileFetcherRemoteUseExisting;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Procrastinator\Result;

/**
 * @covers \Drupal\common\FileFetcher\FileFetcherRemoteUseExisting
 * @coversDefaultClass \Drupal\common\FileFetcher\FileFetcherRemoteUseExisting
 *
 */
class FileFetcherRemoteUseExistingTest extends TestCase {

  /**
   * @covers ::copy
   */
  public function testCopy() {
    $result = new Result();
    $remote = new FileFetcherRemoteUseExisting();

    // Set up a file system.
    $root = vfsStream::setup();
    $file = $root->url() . '/nine_bytes.csv';
    $file_contents = '0123,4567';

    // Config for processor.
    $state = [
      'destination' => $file,
      'source' => 'https://example.com/bad_path.csv',
    ];

    // Run it for error condition because the file doesn't already exist, so it
    // will try to copy the source, but the source URL is bad.
    $result_state = $remote->copy($state, $result);
    $this->assertEquals(Result::ERROR, $result_state['result']->getStatus());

    // Add existing file contents.
    file_put_contents($file, $file_contents);

    // Run it for re-use of existing file. This will succeed because the file
    // is there.
    $result_state = $remote->copy($state, $result);

    $this->assertEquals(Result::DONE, $result_state['result']->getStatus());
    $this->assertEquals(9, $result_state['state']['total_bytes']);
    $this->assertEquals(9, $result_state['state']['total_bytes_copied']);
    $this->assertStringEqualsFile($file, $file_contents);
  }

}
