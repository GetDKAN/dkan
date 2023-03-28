<?php

namespace Drupal\Tests\datastore\Unit\Service\Info;

use Contracts\Mock\Storage\Memory;
use CsvParser\Parser\Csv;
use Drupal\common\FileFetcher\Factory;
use Drupal\datastore\DatastoreResource;
use Drupal\datastore\Plugin\QueueWorker\ImportJob;
use Drupal\common\Storage\JobStore;
use Drupal\common\Storage\JobStoreFactory;
use Drupal\datastore\Service\Info\ImportInfo;
use Drupal\datastore\Service\Info\ImportInfoList;
use Drupal\Tests\datastore\Unit\Plugin\QueueWorker\TestMemStorage;
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

    // Make a FileFetcher object.
    $storage = new Memory();
    $config = [
      "resource" => (new DatastoreResource('id', 'path', 'mime')),
      "storage" => new TestMemStorage(),
      "parser" => Csv::getParser(),
      "filePath" => 'test',
    ];
    $job = FileFetcher::get("1", $storage, $config);

    // Tell the FileFetcher how many bytes it has processed.
    $job->setStateProperty('total_bytes_copied', 1024);

    // Make getBytesProcessed() public.
    $ref_get_bytes_processed = new \ReflectionMethod($import_info, 'getBytesProcessed');
    $ref_get_bytes_processed->setAccessible(TRUE);

    // Assert 0 bytes processed given an unknown job type.
    $this->assertEquals(1024, $ref_get_bytes_processed->invokeArgs($import_info, [$job]));
  }

  /**
   * @covers ::getBytesProcessed
   */
  public function testGetBytesProcessedImportJob() {
    // Mock ImportInfo to disable the constructor.
    $import_info = $this->getMockBuilder(ImportInfo::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['getFileSize'])
      ->getMock();

    // Make an ImportJob object.
    $storage = new Memory();
    $config = [
      "resource" => (new DatastoreResource('id', 'path', 'mime')),
      "storage" => new TestMemStorage(),
      "parser" => Csv::getParser(),
    ];
    $job = ImportJob::get("1", $storage, $config);

    // Make getBytesProcessed() public.
    $ref_get_bytes_processed = new \ReflectionMethod($import_info, 'getBytesProcessed');
    $ref_get_bytes_processed->setAccessible(TRUE);

    // ImportJob::BYTES_PER_CHUNK is 8192. We should get back the smaller of
    // file size vs. chunks * bytes per chunk.
    // First we'll make the file size larger so we should get back the chunked
    // value.
    $import_info->method('getFileSize')
      ->willReturn(8193);
    $job->setStateProperty('chunksProcessed', 1);

    $this->assertEquals(8192, $ref_get_bytes_processed->invokeArgs($import_info, [$job]));

    // Next we'll make the chunked value larger so we should get back the file
    // size.
    $import_info->method('getFileSize')
      ->willReturn(8193);
    $job->setStateProperty('chunksProcessed', 2);

    $this->assertEquals(8193, $ref_get_bytes_processed->invokeArgs($import_info, [$job]));
  }

}
