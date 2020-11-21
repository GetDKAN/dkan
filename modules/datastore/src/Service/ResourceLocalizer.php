<?php

namespace Drupal\datastore\Service;

use Drupal\common\LoggerTrait;
use Drupal\common\Resource;
use Drupal\common\Storage\JobStoreFactory;
use Drupal\common\UrlHostTokenResolver;
use Drupal\common\Util\DrupalFiles;
use Contracts\FactoryInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\metastore\Exception\AlreadyRegistered;
use Drupal\metastore\Reference\Referencer;
use Drupal\metastore\ResourceMapper;
use FileFetcher\FileFetcher;
use Procrastinator\Result;

/**
 * Resource localizer.
 *
 * @todo Update fileMapper to resourceMapper.
 */
class ResourceLocalizer {
  use LoggerTrait;

  const LOCAL_FILE_PERSPECTIVE = 'local_file';
  const LOCAL_URL_PERSPECTIVE = 'local_url';

  private $fileMapper;
  private $fileFetcherFactory;
  private $drupalFiles;
  private $jobStoreFactory;

  /**
   * Constructor.
   */
  public function __construct(ResourceMapper $fileMapper, FactoryInterface $fileFetcherFactory, DrupalFiles $drupalFiles, JobStoreFactory $jobStoreFactory) {
    $this->fileMapper = $fileMapper;
    $this->fileFetcherFactory = $fileFetcherFactory;
    $this->drupalFiles = $drupalFiles;
    $this->jobStoreFactory = $jobStoreFactory;
  }

  /**
   * Retriever the file and create a local copy of it.
   */
  public function localize($identifier, $version = NULL) {
    $resource = $this->getResourceSource($identifier, $version);
    $ff = $this->getFileFetcher($resource);
    return $ff->run();
  }

  /**
   * Get the localized resource.
   */
  public function get($identifier, $version = NULL, $perpective = self::LOCAL_FILE_PERSPECTIVE): ?Resource {
    /** @var \Drupal\common\Resource $resource */
    $resource = $this->getResourceSource($identifier, $version);

    if (!$resource) {
      return NULL;
    }

    $ff = $this->getFileFetcher($resource);

    if ($ff->getResult()->getStatus() != Result::DONE) {
      return NULL;
    }

    $this->registerNewPerspectives($resource, $ff);

    return $this->getFileMapper()->get($resource->getIdentifier(), $perpective, $resource->getVersion());
  }

  /**
   * Private.
   */
  private function registerNewPerspectives(Resource $resource, FileFetcher $fileFetcher) {

    $localFilePath = $fileFetcher->getStateProperty('destination');
    $dir = "file://" . $this->drupalFiles->getPublicFilesDirectory();
    $localFileDrupalUri = str_replace($dir, "public://", $localFilePath);
    $localUrl = $this->drupalFiles->fileCreateUrl($localFileDrupalUri);
    $localUrl = Referencer::hostify($localUrl);

    $new = $resource->createNewPerspective(self::LOCAL_FILE_PERSPECTIVE, $localFilePath);

    try {
      $this->getFileMapper()->registerNewPerspective($new);
    }
    catch (AlreadyRegistered $e) {
    }

    $localUrlPerspective = $resource->createNewPerspective(self::LOCAL_URL_PERSPECTIVE, $localUrl);

    try {
      $this->getFileMapper()->registerNewPerspective($localUrlPerspective);
    }
    catch (AlreadyRegistered $e) {
    }
  }

  /**
   * Get Result.
   */
  public function getResult($identifier, $version = NULL) {
    $ff = $this->getFileFetcher($this->getResourceSource($identifier, $version));
    return $ff->getResult();
  }

  /**
   * Remove.
   */
  public function remove($identifier, $version = NULL) {
    /** @var \Drupal\common\Resource $resource */
    $resource = $this->get($identifier, $version);
    if ($resource) {
      $this->fileMapper->remove($resource);
      if (file_exists($resource->getFilePath())) {
        unlink($resource->getFilePath());
      }
      $this->getJobStoreFactory()->getInstance(FileFetcher::class)->remove($resource->getUniqueIdentifier());
    }
  }

  /**
   * Private.
   */
  private function getResourceSource($identifier, $version = NULL): ?Resource {
    return $this->getFileMapper()->get($identifier, Resource::DEFAULT_SOURCE_PERSPECTIVE, $version);
  }

  /**
   * Get FileFetcher.
   */
  public function getFileFetcher(Resource $resource): FileFetcher {
    $uuid = "{$resource->getIdentifier()}_{$resource->getVersion()}";
    $directory = "public://resources/{$uuid}";
    $this->drupalFiles->getFilesystem()->prepareDirectory($directory, FileSystemInterface::CREATE_DIRECTORY);
    $config = [
      'filePath' => UrlHostTokenResolver::resolve($resource->getFilePath()),
      'temporaryDirectory' => $directory,
    ];
    return $this->fileFetcherFactory->getInstance($uuid, $config);
  }

  /**
   * Private.
   */
  private function getFileMapper(): ResourceMapper {
    return $this->fileMapper;
  }

  /**
   * Private.
   */
  private function getJobStoreFactory() {
    return $this->jobStoreFactory;
  }

}
