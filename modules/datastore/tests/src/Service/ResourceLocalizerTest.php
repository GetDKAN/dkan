<?php

use Drupal\common\Util\DrupalFiles;
use Drupal\datastore\Service\ResourceLocalizer;
use Drupal\metastore\ResourceMapper;
use MockChain\Sequence;
use PHPUnit\Framework\TestCase;
use Drupal\common\Resource;
use Drupal\Core\File\FileSystem;
use MockChain\Chain;
use Drupal\common\Storage\JobStore;
use Drupal\common\Storage\JobStoreFactory;

/**
 *
 */
class ResourceLocalizerTest extends TestCase {

  /**
   *
   */
  public function test() {

    $sequence = (new Sequence())
      ->add(NULL)
      ->add(new Resource('123', 'blah', 'blah', ResourceLocalizer::LOCAL_FILE_PERSPECTIVE))
      ->add(NULL);

    $fileMapper = (new Chain($this))
      ->add(ResourceMapper::class, 'get', $sequence)
      ->add(ResourceMapper::class, 'remove', NULL)
      ->getMock();

    $jobStoreFactory = (new Chain($this))
      ->add(JobStoreFactory::class, 'getInstance', JobStore::class)
      ->add(JobStore::class, 'retrieve', NULL)
      ->add(JobStore::class, 'store', '123')
      ->add(JobStore::class, 'remove', NULL)
      ->getMock();

    $drupalFiles = (new Chain($this))
      ->add(DrupalFiles::class, 'getFileSystem', FileSystem::class)
      ->add(FileSystem::class, 'realpath', __DIR__)
      ->add(FileSystem::class, "prepareDirectory", NULL)
      ->getMock();

    $resource = new Resource('http://hello.world/file.csv', 'text/csv');
    $service = new ResourceLocalizer($fileMapper, $jobStoreFactory, $drupalFiles);
    $this->assertNull($service->get($resource->getIdentifier(), $resource->getVersion()));
  }

}
