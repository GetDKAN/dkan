<?php

namespace Drupal\Tests\datastore\Unit\Service;

use Drupal\common\Resource;
use Drupal\common\Storage\JobStore;
use Drupal\common\Storage\JobStoreFactory;
use Drupal\common\Util\DrupalFiles;
use Drupal\Core\DependencyInjection\Container;
use Drupal\Core\File\FileSystem;
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
   *
   */
  public function testNoResourceFound() {

    $resource = new Resource('http://hello.world/file.csv', 'text/csv');

    $service = new ResourceLocalizer(
      $this->getFileMapperChain()->getMock(),
      $this->getFileFetcherFactoryChain()->getMock(),
      $this->getDrupalFilesChain()->getMock(),
      $this->getJobStoreFactoryChain()->getMock()
    );
    $this->assertNull($service->get($resource->getIdentifier(), $resource->getVersion()));
  }

  /**
   *
   */
  public function testResourceLocalizerRemove() {

    $resource = new Resource('http://hello.world/file.csv', 'text/csv');

    $fileMapper = $this->getFileMapperChain()
      ->add(ResourceMapper::class, 'get', $resource)
      ->getMock();

    $service = new ResourceLocalizer(
      $fileMapper,
      $this->getFileFetcherFactoryChain()->getMock(),
      $this->getDrupalFilesChain()->getMock(),
      $this->getJobStoreFactoryChain()->getMock()
    );

    \Drupal::setContainer($this->getContainer()->getMock());

    $this->assertNull($service->remove($resource->getIdentifier(), $resource->getVersion()));
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
      ->add(DrupalFiles::class, 'fileCreateUrl', 'http://hello.world/file.csv')
      ->add(FileSystem::class, 'realpath', 'http://hello.world/file.csv')
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
      ->index(0);

    $container = (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(RequestStack::class, 'getCurrentRequest', Request::class)
      ->add(Request::class, 'getHost', 'http://hello.world');

    return $container;
  }

}
