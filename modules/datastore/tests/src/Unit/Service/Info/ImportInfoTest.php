<?php

namespace Drupal\Tests\datastore\Unit\Service\Info;

use Drupal\common\FileFetcher\Factory;
use Drupal\datastore\Plugin\QueueWorker\ImportJob;
use Drupal\common\Storage\JobStore;
use Drupal\common\Storage\JobStoreFactory;
use Drupal\datastore\Service\Info\ImportInfo;
use Drupal\datastore\Service\Info\ImportInfoList;
use FileFetcher\FileFetcher;
use MockChain\Chain;
use MockChain\Options;
use PHPUnit\Framework\TestCase;
use Procrastinator\Job\Job;
use Procrastinator\Result;
use Symfony\Component\DependencyInjection\Container;

/**
 * @coversDefaultClass \Drupal\datastore\Service\Info\ImportInfo
 *
 * @group datastore
 * @group dkan-core
 */
class ImportInfoTest extends TestCase {

  /**
   * @covers ::getBytesProcessed
   */
  public function testGetBytesProcessedUnknownClass() {
    // Mock ImportInfo to disable the constructor.
    $import_info = $this->getMockBuilder(ImportInfo::class)
      ->disableOriginalConstructor()
      ->getMock();

    // Job is the abstract class which getBytesProcessed() expects as an
    // argument.
    $job = $this->getMockBuilder(Job::class)
      ->getMockForAbstractClass();

    // Make getBytesProcessed() public.
    $ref_get_bytes_processed = new \ReflectionMethod($import_info, 'getBytesProcessed');
    $ref_get_bytes_processed->setAccessible(TRUE);

    // Assert 0 bytes processed given an unknown job type.
    $this->assertEquals(0, $ref_get_bytes_processed->invokeArgs($import_info, [$job]));
  }

  /**
   * @covers ::getBytesProcessed
   */
  public function testGetBytesProcessedFileFetcher() {
    // Mock ImportInfo to disable the constructor.
    $import_info = $this->getMockBuilder(ImportInfo::class)
      ->disableOriginalConstructor()
      ->getMock();

    // FileFetcher tells you the bytes when you call getStateProperty().
    $job = $this->getMockBuilder(FileFetcher::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['getStateProperty'])
      ->getMock();
    $job->method('getStateProperty')
      ->willReturn(1024);

    // Make getBytesProcessed() public.
    $ref_get_bytes_processed = new \ReflectionMethod($import_info, 'getBytesProcessed');
    $ref_get_bytes_processed->setAccessible(TRUE);

    $this->assertEquals(1024, $ref_get_bytes_processed->invokeArgs($import_info, [$job]));
  }

}
