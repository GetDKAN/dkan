<?php

namespace Drupal\Tests\datastore\Unit\Service;

use Drupal\common\DataResource;
use Drupal\common\Storage\JobStore;
use Drupal\common\Storage\JobStoreFactory;
use Drupal\common\Util\DrupalFiles;
use Drupal\Core\DependencyInjection\Container;
use Drupal\Core\File\FileSystem;
use Drupal\Core\StreamWrapper\PublicStream;
use Drupal\Core\StreamWrapper\StreamWrapperManager;
use Drupal\datastore\Service\ResourceLocalizer;
use Drupal\metastore\ResourceMapper;
use FileFetcher\FileFetcher;
use MockChain\Chain;
use MockChain\Options;
use PHPUnit\Framework\TestCase;
use Procrastinator\Result;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 *
 */
class ResourceLocalizerTest extends TestCase {
  /**
   * HTTP host protocol and domain for testing download URL.
   *
   * @var string
   */
  const HOST = 'http://example.com';

  /**
   *
   */
  public function testNoResourceFound() {

    $resource = new DataResource(self::HOST . '/file.csv', 'text/csv');

    $service = new ResourceLocalizer(
      $this->getFileMapperChain()->getMock(),
      $this->getFileFetcherFactoryChain()->getMock(),
      $this->getDrupalFilesChain()->getMock(),
      $this->getJobStoreFactoryChain()->getMock()
    );
    $this->assertNull($service->get($resource->getIdentifier(), $resource->getVersion()));
  }

  /**
   * Test removal of a local resource file.
   */
  public function testResourceLocalizerRemove(): void {
    $this->callWithTmpFile([$this, 'doTestResourceLocalizerRemove']);
  }

  /**
   * Test removal of the given resource file.
   *
   * @param string $file_path
   *   Path to resource file for testing DKAN resource creation and removal.
   */
  private function doTestResourceLocalizerRemove(string $file_path): void {

    $resource = new DataResource(self::HOST . '/file.csv', 'text/csv');

    $fileMapper = $this->getFileMapperChain()
      ->add(ResourceMapper::class, 'get', $resource)
      ->getMock();

    $fileFetcher = $this->getFileFetcherFactoryChain()
      ->add(FileFetcher::class, 'getStateProperty', $file_path)
      ->getMock();

    $service = new ResourceLocalizer(
      $fileMapper,
      $fileFetcher,
      $this->getDrupalFilesChain()->getMock(),
      $this->getJobStoreFactoryChain()->getMock()
    );

    \Drupal::setContainer($this->getContainer()->getMock());

    $this->assertNull($service->remove($resource->getIdentifier(), $resource->getVersion()));
  }

  /**
   * Call the supplied function with a temp file.
   *
   * @param callable $function
   *   The function being called.
   * @param string $content
   *   Optional content for the file being created.
   *
   * @return mixed
   *   The result of calling the supplied function.
   */
  private function callWithTmpFile(callable $function, string $content = '') {
    // Create a temp file.
    $file = tmpfile();
    if ($file === FALSE) {
      throw new \UnexpectedValueException('Unable to create tmp file using `tmpfile()`.');
    }
    // Write the supplied file content to the file.
    fwrite($file, $content);
    // Extract the path from the supplied file.
    $file_metadata = stream_get_meta_data($file);
    $file_path = $file_metadata['uri'];
    // Call the supplied function with the created file.
    $result = $function($file_path);
    // Close and delete the file.
    fclose($file);
    // Return the result.
    return $result;
  }

  /**
   *
   */
  private function getFileMapperChain() {
    return (new Chain($this))
      ->add(ResourceMapper::class, 'get', NULL)
      ->add(ResourceMapper::class, 'remove', NULL);
  }

  /**
   *
   */
  private function getFileFetcherFactoryChain() {
    return (new Chain($this))
      ->add(JobStoreFactory::class, 'getInstance', FileFetcher::class)
      ->add(FileFetcher::class, 'getResult', Result::class)
      ->add(Result::class, 'getStatus', Result::DONE);
  }

  /**
   *
   */
  private function getDrupalFilesChain() {
    return (new Chain($this))
      ->add(DrupalFiles::class, 'getFileSystem', FileSystem::class)
      ->add(FileSystem::class, 'prepareDirectory', NULL)
      ->add(DrupalFiles::class, 'fileCreateUrl', self::HOST . '/file.csv')
      ->add(FileSystem::class, 'realpath', self::HOST . '/file.csv')
      ->add(DrupalFiles::class, 'getStreamWrapperManager', StreamWrapperManager::class);
  }

  /**
   *
   */
  private function getJobStoreFactoryChain() {
    return (new Chain($this))
      ->add(JobStoreFactory::class, 'getInstance', JobStore::class)
      ->add(JobStore::class, 'remove', NULL);
  }

  /**
   * Seems necessary for one line in ResourceLocalizer.
   *
   * $localUrl = Referencer::hostify($localUrl);
   */
  private function getContainer() {
    $options = (new Options())
      ->add('request_stack', RequestStack::class)
      ->add('stream_wrapper_manager', StreamWrapperManager::class)
      ->index(0);

    $container = (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(StreamWrapperManager::class, 'getViaUri', PublicStream::class)
      ->add(PublicStream::class, 'getExternalUrl', self::HOST)
      ->add(RequestStack::class, 'getCurrentRequest', Request::class)
      ->add(Request::class, 'getHost', self::HOST);

    return $container;
  }

}
