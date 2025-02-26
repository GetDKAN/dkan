<?php

namespace Drupal\Tests\common\Unit\Util;

use Drupal\common\Util\DrupalFiles;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Http\ClientFactory;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\StreamWrapper\StreamWrapperInterface;
use Drupal\Core\StreamWrapper\StreamWrapperManager;
use Drupal\file\FileRepository;
use MockChain\Chain;
use MockChain\Options;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @group dkan
 * @group common
 * @group unit
 */
class DrupalFilesTest extends TestCase {

  /**
   * Basic local retrieve test.
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
      ->add('file.repository', FileRepository::class)
      ->add('stream_wrapper_manager', StreamWrapperManager::class)
      ->add('http_client_factory', ClientFactory::class)
      ->add('dkan.common.logger_channel', LoggerChannelInterface::class)
      ->index(0);

    return (new Chain($this))
      ->add(ContainerInterface::class, 'get', $options)
      ->add(FileSystemInterface::class, 'realpath', "/tmp")
      ->add(StreamWrapperManager::class, 'getViaUri', StreamWrapperInterface::class)
      ->add(StreamWrapperInterface::class, 'getExternalUrl', "blah")
      ->getMock();
  }

  /**
   * Protected.
   */
  protected function tearDown(): void {
    parent::tearDown();
    unlink("/tmp/hello.txt");
  }

}
