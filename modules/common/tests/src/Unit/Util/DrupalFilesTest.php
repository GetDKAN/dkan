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

  /**
   *
   */
  public function test() {
    $drupalFiles = DrupalFiles::create($this->getContainer());
    $drupalFiles->retrieveFile(
      "file://" . __DIR__ . "/../../../files/hello.txt",
      "public://tmp");
    $this->assertTrue(file_exists("/tmp/hello.txt"));
  }

  /**
   *
   */
  public function testUnsupportedOriginUrl() {
    $drupalFiles = DrupalFiles::create($this->getContainer());
    $this->expectExceptionMessage("Only file:// and http(s) urls are supported");
    $drupalFiles->retrieveFile(
      "s3://foo/bar/hello.txt",
      "public://tmp");
  }

  /**
   *
   */
  public function testUnsupportedDestination() {
    $drupalFiles = DrupalFiles::create($this->getContainer());
    $this->expectExceptionMessage("Only moving files to Drupal's public directory (public://) is supported");
    $drupalFiles->retrieveFile(
      "file://" . __DIR__ . "/../../../files/hello.txt",
      "private://tmp");
  }

  /**
   * Private.
   */
  private function getContainer(): ContainerInterface {
    $options = (new Options())
      ->add('file_system', FileSystemInterface::class)
      ->add('stream_wrapper_manager', StreamWrapperManager::class)
      ->index(0);

    $container = (new Chain($this))
      ->add(ContainerInterface::class, 'get', $options)
      ->add(FileSystemInterface::class, 'realpath', "/tmp")
      ->add(StreamWrapperManager::class, 'getViaUri', StreamWrapperInterface::class)
      ->add(StreamWrapperInterface::class, 'getExternalUrl', "blah")
      ->getMock();

    return $container;
  }

  /**
   * Protected.
   */
  protected function tearDown() {
    $file = '/tmp/hello.txt';
    if (file_exists($file)) {
      unlink($file);
    }
  }

}
