<?php

namespace Drupal\Tests\dkan_harvest\Unit\Storage;

use Drupal\dkan_common\Tests\DkanTestBase;
use Drupal\dkan_harvest\Storage\File;
use Drupal\dkan_harvest\Load\IFileHelper;

/**
 * Tests Drupal\dkan_harvest\Storage\File.
 *
 * @coversDefaultClass Drupal\dkan_harvest\Storage\File
 * @group dkan_harvest
 */
class FileTest extends DkanTestBase {

  /**
   *
   */
  public function setUp() {
    parent::setUp();

    // `file.inc` isn't loaded during unit tests
    // some constants are missing.
    $consts = [
      'FILE_CREATE_DIRECTORY'   => 1,
      'FILE_MODIFY_PERMISSIONS' => 2,
    ];
    foreach ($consts as $name => $value) {
      if (!defined($name)) {
        define($name, $value);
      }
    }
  }

  /**
   *
   */
  public function testConstruct() {
    // Setup.
    $mock = $this->getMockBuilder(File::class)
      ->setMethods(['getFileHelper'])
      ->disableOriginalConstructor()
      ->getMock();

    $mockFileHelper = $this->getMockBuilder(IFileHelper::class)
      ->setMethods(['prepareDir'])
      ->getMockForAbstractClass();

    $directoryPath = '/foobar';

    // Expect.
    $mock->expects($this->once())
      ->method('getFileHelper')
      ->willReturn($mockFileHelper);

    $mockFileHelper->expects($this->once())
      ->method('prepareDir')
      ->with(
                    $directoryPath,
                    FILE_CREATE_DIRECTORY | FILE_MODIFY_PERMISSIONS
    );

    // Assert.
    $mock->__construct($directoryPath);
    $this->assertEquals($directoryPath, $this->readAttribute($mock, 'directoryPath'));
  }

  /**
   *
   */
  public function testRetrieve() {
    // Setup.
    $mock = $this->getMockBuilder(File::class)
      ->setMethods(['getFileHelper'])
      ->disableOriginalConstructor()
      ->getMock();

    $mockFileHelper = $this->getMockBuilder(IFileHelper::class)
      ->setMethods(['fileGetContents'])
      ->getMockForAbstractClass();

    $id            = 'foo-file';
    $fileContents  = '{foo-file-contents}';
    $directoryPath = '/foobar';
    $this->writeProtectedProperty($mock, 'directoryPath', $directoryPath);

    // Expect.
    $mock->expects($this->once())
      ->method('getFileHelper')
      ->willReturn($mockFileHelper);

    $mockFileHelper->expects($this->once())
      ->method('fileGetContents')
      ->with("{$directoryPath}/{$id}.json")
      ->willReturn($fileContents);

    // Assert.
    $actual = $mock->retrieve($id);
    $this->assertEquals($fileContents, $actual);
  }

  /**
   *
   */
  public function testStore() {
    // Setup.
    $mock = $this->getMockBuilder(File::class)
      ->setMethods(['getFileHelper'])
      ->disableOriginalConstructor()
      ->getMock();

    $mockFileHelper = $this->getMockBuilder(IFileHelper::class)
      ->setMethods(['filePutContents'])
      ->getMockForAbstractClass();

    $id            = 'foo-file';
    $data          = '{foo-file-contents}';
    $directoryPath = '/foobar';
    $this->writeProtectedProperty($mock, 'directoryPath', $directoryPath);

    // Expect.
    $mock->expects($this->once())
      ->method('getFileHelper')
      ->willReturn($mockFileHelper);

    $mockFileHelper->expects($this->once())
      ->method('filePutContents')
      ->with("{$directoryPath}/{$id}.json", $data);

    // Assert.
    $mock->store($data, $id);
  }

  /**
   *
   */
  public function testRemove() {
    // Setup.
    $mock = $this->getMockBuilder(File::class)
      ->setMethods(['getFileHelper'])
      ->disableOriginalConstructor()
      ->getMock();

    $mockFileHelper = $this->getMockBuilder(IFileHelper::class)
      ->setMethods(['fileDelete'])
      ->getMockForAbstractClass();

    $id            = 'foo-file';
    $directoryPath = '/foobar';
    $this->writeProtectedProperty($mock, 'directoryPath', $directoryPath);

    // Expect.
    $mock->expects($this->once())
      ->method('getFileHelper')
      ->willReturn($mockFileHelper);

    $mockFileHelper->expects($this->once())
      ->method('fileDelete')
      ->with("{$directoryPath}/{$id}.json");

    // Assert.
    $mock->remove($id);
  }

  /**
   *
   */
  public function testRetrieveAll() {
    // Setup.
    $mock = $this->getMockBuilder(File::class)
      ->setMethods(['getFileHelper'])
      ->disableOriginalConstructor()
      ->getMock();

    $mockFileHelper = $this->getMockBuilder(IFileHelper::class)
      ->setMethods([
        'fileGlob',
        'fileGetContents',
      ])
      ->getMockForAbstractClass();

    $glob = [
      'foo-1.json',
      'foo-2.json',
    ];

    $fileContents = [
      '{foo-1-contents}',
      '{foo-2-contents}',
    ];

    $expected = [
      'foo-1' => '{foo-1-contents}',
      'foo-2' => '{foo-2-contents}',
    ];
    $loopCount = count($glob);

    $directoryPath = '/foobar';
    $this->writeProtectedProperty($mock, 'directoryPath', $directoryPath);

    // Expect.
    $mock->expects($this->once())
      ->method('getFileHelper')
      ->willReturn($mockFileHelper);

    $mockFileHelper->expects($this->once())
      ->method('fileGlob')
      ->with("{$directoryPath}/*.json")
      ->willReturn($glob);

    $mockFileHelper->expects($this->exactly($loopCount))
      ->method('fileGetContents')
      ->withConsecutive(
                    [$glob[0]],
                    [$glob[1]]
            )
      ->willReturnOnConsecutiveCalls(
                    $fileContents[0],
                    $fileContents[1]
    );

    // Assert.
    $actual = $mock->retrieveAll();
    $this->assertEquals($expected, $actual);
  }

  /**
   *
   */
  public function testRetrieveAllNoFiles() {
    // Setup.
    $mock = $this->getMockBuilder(File::class)
      ->setMethods(['getFileHelper'])
      ->disableOriginalConstructor()
      ->getMock();

    $mockFileHelper = $this->getMockBuilder(IFileHelper::class)
      ->setMethods([
        'fileGlob',
        'fileGetContents',
      ])
      ->getMockForAbstractClass();

    $glob     = [];
    $expected = [];

    $directoryPath = '/foobar';
    $this->writeProtectedProperty($mock, 'directoryPath', $directoryPath);

    // Expect.
    $mock->expects($this->once())
      ->method('getFileHelper')
      ->willReturn($mockFileHelper);

    $mockFileHelper->expects($this->once())
      ->method('fileGlob')
      ->with("{$directoryPath}/*.json")
      ->willReturn($glob);

    $mockFileHelper->expects($this->never())
      ->method('fileGetContents');

    // Assert.
    $actual = $mock->retrieveAll();
    $this->assertEquals($expected, $actual);
  }

}
