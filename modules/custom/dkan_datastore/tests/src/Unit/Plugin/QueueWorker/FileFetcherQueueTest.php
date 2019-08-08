<?php

namespace Drupal\Tests\dkan_datastore\Unit\Plugin\QueueWorker;

use Drupal\dkan_datastore\Plugin\QueueWorker\FileFetcherQueue;
use Drupal\dkan_common\Tests\DkanTestBase;
use Drupal\Core\Queue\QueueInterface;
use Drupal\Core\Queue\SuspendQueueException;

/**
 * @coversDefaultClass Drupal\dkan_datastore\Plugin\QueueWorker\FileFetcherQueue
 * @group              dkan_datastore
 */
class FileFetcherQueueTest extends DkanTestBase {

  /**
   * Tests ProcessItem().
   */
  public function testProcessItem() {
    // Setup.
    $mock = $this->getMockBuilder(FileFetcherQueue::class)
      ->setMethods(
              [
                'fetchFile',
                'getImporterQueue',
                'sanitizeString',
                'isFileTemporary',
              ]
          )
      ->disableOriginalConstructor()
      ->getMock();

    $mockQueue = $this->getMockBuilder(QueueInterface::class)
      ->setMethods(['createItem'])
      ->disableOriginalConstructor()
      ->getMockForAbstractClass();

    $uuid           = uniqid('uuid');
    $resourceId     = uniqid('resource_id');
    $filePath       = uniqid('file_path');
    $actualFilePath = uniqid('actual_file_path');
    $importConfig   = [uniqid('import_config')];
    $fileIdentifier = [uniqid('file_identifier')];
    $isTemporary    = FALSE;

    $data = [
      'uuid'          => $uuid,
      'resource_id'   => $resourceId,
      'file_path'     => $filePath,
      'import_config' => $importConfig,
    ];

    $expected = uniqid('new-queue-id');

    // Expect.
    $mock->expects($this->once())
      ->method('fetchFile')
      ->with($uuid, $filePath)
      ->willReturn($actualFilePath);

    $mock->expects($this->once())
      ->method('getImporterQueue')
      ->willReturn($mockQueue);

    $mock->expects($this->once())
      ->method('sanitizeString')
      ->with($actualFilePath)
      ->willReturn($fileIdentifier);

    $mock->expects($this->once())
      ->method('isFileTemporary')
      ->with($actualFilePath)
      ->willReturn($isTemporary);

    $mockQueue->expects($this->once())
      ->method('createItem')
      ->with(
              [
                'uuid'              => $uuid,
                'resource_id'       => $resourceId,
                'file_identifier'   => $fileIdentifier,
                'file_path'         => $actualFilePath,
                'import_config'     => $importConfig,
                'file_is_temporary' => $isTemporary,
              ]
          )
      ->willReturn($expected);

    // Assert.
    $actual = $mock->processItem($data);
    $this->assertEquals($expected, $actual);
  }

  /**
   * Tests FetchFile().
   */
  public function testFetchFile() {
    // Setup.
    $mock = $this->getMockBuilder(FileFetcherQueue::class)
      ->setMethods(
              [
                'getFileObject',
                'getTemporaryFile',
                'fileCopy',
              ]
          )
      ->disableOriginalConstructor()
      ->getMock();

    $mockSource = $this->getMockBuilder(\SplFileObject::class)
      ->setMethods([
        'isFile',
      ])
      // Throws logicexception otherwise
      // see https://stackoverflow.com/a/24425928
      ->setConstructorArgs(['php://memory'])
      ->getMock();

    $mockDest = $this->getMockBuilder(\SplFileObject::class)
      ->setMethods([])
      ->setConstructorArgs(['php://memory'])
      ->getMock();

    $uuid     = uniqid('uuid');
    $filePath = uniqid('file_path');
    $tmpFile  = uniqid('tmp_path');
    $isFile   = FALSE;

    // Expect.
    $mock->expects($this->exactly(2))
      ->method('getFileObject')
      ->withConsecutive(
              [$filePath, 'r'],
              [$tmpFile, 'w']
          )
      ->willReturnOnConsecutiveCalls(
              $mockSource,
              $mockDest
          );

    $mockSource->expects($this->once())
      ->method('isFile')
      ->willReturn($isFile);

    $mock->expects($this->once())
      ->method('getTemporaryFile')
      ->with($uuid)
      ->willReturn($tmpFile);

    $mock->expects($this->once())
      ->method('fileCopy')
      ->with($mockSource, $mockDest);

    // Assert.
    $actual = $this->invokeProtectedMethod($mock, 'fetchFile', $uuid, $filePath);
    $this->assertEquals($tmpFile, $actual);
  }

  /**
   * Tests FetchFile() if local file.
   */
  public function testFetchFileIfLocalFile() {
    // Setup.
    $mock = $this->getMockBuilder(FileFetcherQueue::class)
      ->setMethods(
              [
                'getFileObject',
                'getTemporaryFile',
                'fileCopy',
              ]
          )
      ->disableOriginalConstructor()
      ->getMock();

    $mockSource = $this->getMockBuilder(\SplFileObject::class)
      ->setMethods([
        'isFile',
      ])
      // Throws logicexception otherwise.
      ->setConstructorArgs(['php://memory'])
      ->getMock();

    $uuid     = uniqid('uuid');
    $filePath = uniqid('file_path');
    $isFile   = TRUE;

    // Expect.
    $mock->expects($this->once())
      ->method('getFileObject')
      ->with($filePath)
      ->willReturn($mockSource);

    $mockSource->expects($this->once())
      ->method('isFile')
      ->willReturn($isFile);

    $mock->expects($this->never())
      ->method('getTemporaryFile');

    $mock->expects($this->never())
      ->method('fileCopy');

    // Assert.
    $actual = $this->invokeProtectedMethod($mock, 'fetchFile', $uuid, $filePath);
    $this->assertEquals($filePath, $actual);
  }

  /**
   * Tests FetchFile() on error.
   */
  public function testFetchFileCatchException() {
    // Setup.
    $mock = $this->getMockBuilder(FileFetcherQueue::class)
      ->setMethods(
              [
                'getFileObject',
              ]
          )
      ->disableOriginalConstructor()
      ->getMock();

    $uuid     = uniqid('uuid');
    $filePath = uniqid('file_path');

    $exceptionMessage = 'something went wrong';

    // Expect.
    $mock->expects($this->once())
      ->method('getFileObject')
      ->with($filePath)
      ->willThrowException(new \Exception($exceptionMessage));

    $this->setExpectedException(SuspendQueueException::class, "Unable to fetch {$filePath} for resource {$uuid}. Reason: " . $exceptionMessage);
    // Assert.
    $this->invokeProtectedMethod($mock, 'fetchFile', $uuid, $filePath);
  }

  /**
   * Tests FileCopy().
   */
  public function testFileCopy() {
    // Setup.
    $mock = $this->getMockBuilder(FileFetcherQueue::class)
      ->setMethods(NULL)
      ->disableOriginalConstructor()
      ->getMock();

    $mockSource = $this->getMockBuilder(\SplFileObject::class)
      ->setMethods([
        'valid',
        'fread',
      ])
      // Throws logicexception otherwise
      // see https://stackoverflow.com/a/24425928
      ->setConstructorArgs(['php://memory'])
      ->getMock();

    $mockDest = $this->getMockBuilder(\SplFileObject::class)
      ->setMethods([
        'fwrite',
      ])
      ->setConstructorArgs(['php://memory'])
      ->getMock();

    // Test the loop as well. provide values for the various stages.
    $test = [
      [
        'fread'  => 'abcd',
        'fwrite' => 4,
      ],
      [
        'fread'  => 'def',
        'fwrite' => 3,
      ],
    ];

    // Total bytes written.
    $expected = 7;

    // Expect.
    $mockSource->expects($this->exactly(3))
      ->method('valid')
        // 2 valid loop, one to exit.
      ->willReturnOnConsecutiveCalls(
        TRUE,
        TRUE,
        FALSE
    );

    $mockSource->expects($this->exactly(2))
      ->method('fread')
      ->with(128 * 1024)
      ->willReturnOnConsecutiveCalls(
              $test[0]['fread'],
              $test[1]['fread']
          );

    $mockDest->expects($this->exactly(2))
      ->method('fwrite')
      ->withConsecutive(
              [$test[0]['fread']],
              [$test[1]['fread']]
          )
      ->willReturnOnConsecutiveCalls(
              $test[0]['fwrite'],
              $test[1]['fwrite']
          );

    // Assert.
    $actual = $this->invokeProtectedMethod($mock, 'fileCopy', $mockSource, $mockDest);
    $this->assertEquals($expected, $actual);
  }

  /**
   * Tests FileCopy on read exception.
   */
  public function testFileCopyReadException() {
    // Setup.
    $mock = $this->getMockBuilder(FileFetcherQueue::class)
      ->setMethods(NULL)
      ->disableOriginalConstructor()
      ->getMock();

    $mockSource = $this->getMockBuilder(\SplFileObject::class)
      ->setMethods([
        'valid',
        'fread',
        'getPath',
      ])
      // Throws logicexception otherwise
      // see https://stackoverflow.com/a/24425928
      ->setConstructorArgs(['php://memory'])
      ->getMock();

    $mockDest = $this->getMockBuilder(\SplFileObject::class)
      ->setMethods([
        'fwrite',
      ])
      ->setConstructorArgs(['php://memory'])
      ->getMock();

    $path = uniqid('path');
    $read = FALSE;

    // Expect.
    $mockSource->expects($this->once())
      ->method('valid')
      ->willReturn(TRUE);

    $mockSource->expects($this->once())
      ->method('fread')
      ->with(128 * 1024)
      ->willReturn($read);

    $mockSource->expects($this->once())
      ->method('getPath')
      ->willReturn($path);

    $mockDest->expects($this->never())
      ->method('fwrite');

    $this->setExpectedException(\RuntimeException::class, "Failed to read from source " . $path);

    // Assert.
    $this->invokeProtectedMethod($mock, 'fileCopy', $mockSource, $mockDest);
  }

  /**
   * Tests FileCopy on Write exception.
   */
  public function testFileCopyWriteException() {
    // Setup.
    $mock = $this->getMockBuilder(FileFetcherQueue::class)
      ->setMethods(NULL)
      ->disableOriginalConstructor()
      ->getMock();

    $mockSource = $this->getMockBuilder(\SplFileObject::class)
      ->setMethods([
        'valid',
        'fread',
      ])
      // Throws logicexception otherwise
      // see https://stackoverflow.com/a/24425928
      ->setConstructorArgs(['php://memory'])
      ->getMock();

    $mockDest = $this->getMockBuilder(\SplFileObject::class)
      ->setMethods(
              [
                'fwrite',
                'getPath',
              ]
          )
      ->setConstructorArgs(['php://memory'])
      ->getMock();

    $path = uniqid('path');
    $read = uniqid('read');
    $write = 0;

    // Expect.
    $mockSource->expects($this->once())
      ->method('valid')
      ->willReturn(TRUE);

    $mockSource->expects($this->once())
      ->method('fread')
      ->with(128 * 1024)
      ->willReturn($read);

    $mockDest->expects($this->once())
      ->method('fwrite')
      ->with($read)
      ->willReturn($write);

    $mockDest->expects($this->once())
      ->method('getPath')
      ->willReturn($path);

    $this->setExpectedException(\RuntimeException::class, "Failed to write to destination " . $path);

    // Assert.
    $this->invokeProtectedMethod($mock, 'fileCopy', $mockSource, $mockDest);
  }

  /**
   * Tests GetTemporaryFile().
   */
  public function testGetTemporaryFile() {
    // Setup.
    $mock = $this->getMockBuilder(FileFetcherQueue::class)
      ->setMethods(
              [
                'getTemporaryDirectory',
                'sanitizeString',
              ]
          )
      ->disableOriginalConstructor()
      ->getMock();

    $uuid      = uniqid('uuid');
    $tmpDir    = uniqid('tmpdir');
    $sanitised = $uuid . 'sanitised';
    $expected  = $tmpDir . '/dkan-resource-' . $sanitised;

    // Expect.
    $mock->expects($this->once())
      ->method('getTemporaryDirectory')
      ->willReturn($tmpDir);
    $mock->expects($this->once())
      ->method('sanitizeString')
      ->with($uuid)
      ->willReturn($sanitised);

    // Assert.
    $actual = $this->invokeProtectedMethod($mock, 'getTemporaryFile', $uuid);
    $this->assertEquals($expected, $actual);
  }

  /**
   *
   * @return array Array of array of arguments
   */
  public function dataIsFileTemporary() {
    $tmpDir = uniqid('/temp/foo');
    return [
      [
        $tmpDir . uniqid('/dkan-resource-'),
        $tmpDir,
        TRUE,
      ],
      [
        $tmpDir . uniqid('/not-true'),
        $tmpDir,
        FALSE,
      ],
    ];
  }

  /**
   * Tests IsFileTemporary().
   *
   * @dataProvider dataIsFileTemporary
   */
  public function testIsFileTemporary($filePath, $tmpDir, $expected) {
    // Setup.
    $mock = $this->getMockBuilder(FileFetcherQueue::class)
      ->setMethods(
              [
                'getTemporaryDirectory',
              ]
          )
      ->disableOriginalConstructor()
      ->getMock();

    // Expects.
    $mock->expects($this->once())
      ->method('getTemporaryDirectory')
      ->willReturn($tmpDir);

    // Assert.
    $actual = $this->invokeProtectedMethod($mock, 'isFileTemporary', $filePath);

    $this->assertEquals($expected, $actual);
  }

  /**
   * Tests getImporterQueue().
   */
  public function testGetImporterQueue() {
    // Setup.
    $mock = $this->getMockBuilder(FileFetcherQueue::class)
      ->setMethods(NULL)
      ->disableOriginalConstructor()
      ->getMock();

    $mockQueueFactory = $this->getMockBuilder(QueueFactory::class)
      ->setMethods(
              [
                'get',
              ]
          )
      ->disableOriginalConstructor()
      ->getMock();
    $this->setActualContainer(
          [
            'queue' => $mockQueueFactory,
          ]
      );

    $mockQueue = $this->createMock(QueueInterface::class);

    $pluginId = 'dkan_datastore_import_queue';

    // Expect.
    $mockQueueFactory->expects($this->once())
      ->method('get')
      ->with($pluginId)
      ->willReturn($mockQueue);

    // Assert.
    $actual = $this->invokeProtectedMethod($mock, 'getImporterQueue');
    $this->assertSame($mockQueue, $actual);
  }

  /**
   * Data provider for testSanitizeString.
   *
   * @return array
   *   Array of arguments.
   */
  public function dataSanitizeString() {
    return [
      ['', ''],
      ['ABCD EFG', 'abcd_efg'],
      // This may be cause for concern? or not.
      ['123-ABCD *&(*%^@*(#&%)*&%               -=*/-+ ;.[[] EFG', '123_abcd_efg'],
    ];
  }

  /**
   * Tests SanitizeString().
   *
   * @dataProvider dataSanitizeString
   */
  public function testSanitizeString($string, $expected) {
    // Setup.
    $mock = $this->getMockBuilder(FileFetcherQueue::class)
      ->setMethods(NULL)
      ->disableOriginalConstructor()
      ->getMock();

    // Assert.
    $actual = $this->invokeProtectedMethod($mock, 'sanitizeString', $string);
    $this->assertSame($expected, $actual);
  }

}
