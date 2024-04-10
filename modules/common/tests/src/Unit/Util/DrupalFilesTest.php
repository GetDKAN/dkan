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
  protected function tearDown(): void {
    unlink("/tmp/hello.txt");
  }

}
