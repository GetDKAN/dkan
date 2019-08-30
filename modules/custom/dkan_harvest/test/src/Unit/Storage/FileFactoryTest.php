<?php

namespace Drupal\Tests\dkan_harvest\Unit\Storage;

use Drupal\Core\File\FileSystem;
use Drupal\dkan_harvest\Storage\File;
use Drupal\dkan_harvest\Storage\FileFactory;
use PHPUnit\Framework\TestCase;

/**
 *
 */
class FileFactoryTest extends TestCase {

  /**
   *
   */
  public function test() {
    $factory = $this->getMockBuilder(FileFactory::class)
      ->setConstructorArgs([$this->getFileSystemMock()])
      ->setMethods(['getFileStorage'])
      ->getMock();

    $factory->method('getFileStorage')->willReturn($this->getFileMock());

    $fileStorage = $factory->getInstance('blah');
    $fileStorage2 = $factory->getInstance('blah');
    $this->assertEquals($fileStorage, $fileStorage2);
  }

  /**
   *
   */
  private function getFileMock() {
    $mock = $this->getMockBuilder(File::class)
      ->disableOriginalConstructor()
      ->getMock();
    return $mock;
  }

  /**
   *
   */
  private function getFileSystemMock() {
    $mock = $this->createMock(FileSystem::class);
    return $mock;
  }

}
