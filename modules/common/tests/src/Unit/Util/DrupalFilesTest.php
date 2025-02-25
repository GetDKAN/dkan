<?php

namespace Drupal\Tests\common\Unit\Util;

use Drupal\common\Util\DrupalFiles;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\StreamWrapper\StreamWrapperInterface;
use Drupal\Core\StreamWrapper\StreamWrapperManager;
use MockChain\Chain;
use MockChain\Options;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 *
 */
class DrupalFilesTest extends TestCase {

  public function testLocalFile() {
    $drupalFiles = DrupalFiles::create($this->getContainer());
    $drupalFiles->retrieveFile(
      "file://" . __DIR__ . "/../../../files/hello.txt",
      "public://tmp");
    $this->assertTrue(file_exists("/tmp/hello.txt"));
  }

  public function testBadScheme() {
    $drupalFiles = DrupalFiles::create($this->getContainer());
    $this->expectExceptionMessage("Only file:// and http(s) urls are supported");
    $drupalFiles->retrieveFile(
      "public://hello.txt",
      "public://tmp");
    $this->assertTrue(file_exists("/tmp/hello.txt"));
  }

  public function testBadDestination() {
    $drupalFiles = DrupalFiles::create($this->getContainer());
    $this->expectExceptionMessage("Only moving files to Drupal's public directory (public://) is supported");
    $drupalFiles->retrieveFile(
      "file://hello.txt",
      "file://tmp");
    $this->assertTrue(file_exists("/tmp/hello.txt"));
  }

  /**
   * Added after system_retrieve_file was deprecated.
   */
  public function testRemoteFile() {
    $drupalFiles = DrupalFiles::create($this->getContainer());
    $this->expectExceptionMessage("Remote file retrieval not yet supported");
    $drupalFiles->retrieveFile(
      "https://web/hello.txt",
      "public://tmp/hello.txt");
    $this->assertTrue(file_exists("/tmp/hello.txt"));
  }

  private function getContainer(): ContainerInterface {
    $options = (new Options())
      ->add('file_system', FileSystemInterface::class)
      ->add('stream_wrapper_manager', StreamWrapperManager::class)
      ->index(0);

    return (new Chain($this))
      ->add(ContainerInterface::class, 'get', $options)
      ->add(FileSystemInterface::class, 'realpath', "/tmp")
      ->add(StreamWrapperManager::class, 'getViaUri', StreamWrapperInterface::class)
      ->add(StreamWrapperInterface::class, 'getExternalUrl', "blah")
      ->getMock();
  }

  protected function tearDown(): void {
    parent::tearDown();
    if (file_exists("/tmp/hello.txt")) {
      unlink("/tmp/hello.txt");
    }
  }

}
