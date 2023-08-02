<?php

namespace Drupal\datastore\Service;

use Drupal\common\FileFetcher\FileFetcherFactory;
use Drupal\common\LoggerTrait;
use Drupal\common\DataResource;
use Drupal\common\Storage\JobStoreFactory;
use Drupal\common\UrlHostTokenResolver;
use Drupal\common\Util\DrupalFiles;
use Drupal\Core\File\FileSystemInterface;
use Drupal\metastore\Exception\AlreadyRegistered;
use Drupal\metastore\Reference\Referencer;
use Drupal\metastore\ResourceMapper;
use Drupal\metastore\Storage\Data;
use FileFetcher\FileFetcher;
use Procrastinator\Result;
use Drupal\common\EventDispatcherTrait;

/**
 * Resource localizer.
 */
class ResourceLocalizer {

  use LoggerTrait;
  use EventDispatcherTrait;

  /**
   * Perspective representing the local file with public:// URI scheme.
   *
   * @var string
   */
  const LOCAL_FILE_PERSPECTIVE = 'local_file';

  /**
   * Perspective representing local file with http:// scheme and bogus domain.
   *
   * @var string
   */
  const LOCAL_URL_PERSPECTIVE = 'local_url';

  /**
   * DKAN resource file mapper service.
   *
   * @var \Drupal\metastore\ResourceMapper
   */
  private ResourceMapper $resourceMapper;

  /**
   * DKAN resource file fetcher factory.
   *
   * @var \Drupal\common\FileFetcher\FileFetcherFactory
   */
  private FileFetcherFactory $fileFetcherFactory;

  /**
   * Drupal files utility service.
   *
   * @var \Drupal\common\Util\DrupalFiles
   */
  private DrupalFiles $drupalFiles;

  /**
   * Job store factory.
   *
   * @var \Drupal\common\Storage\JobStoreFactory
   */
  private JobStoreFactory $jobStoreFactory;

  /**
   * Constructor.
   */
  public function __construct(
    ResourceMapper $fileMapper,
    FileFetcherFactory $fileFetcherFactory,
    DrupalFiles $drupalFiles,
    JobStoreFactory $jobStoreFactory
  ) {
    $this->resourceMapper = $fileMapper;
    $this->fileFetcherFactory = $fileFetcherFactory;
    $this->drupalFiles = $drupalFiles;
    $this->jobStoreFactory = $jobStoreFactory;
  }

  /**
   * Copy the source file to the local file system.
   */
  public function localize($identifier, $version = NULL): ?Result {
    if ($resource = $this->getResourceSource($identifier, $version)) {
      $ff = $this->getFileFetcher($resource);

      // Does the file already exist?
      $file_path = $ff->getStateProperty('file_path');
      if (file_exists($file_path)) {
        // The file exists so we can mark it done.
        throw new \Exception('ITS DONE');
        // $ff->getResult()->setStatus(Result::DONE);
      }
      return $ff->run();
    }
    return NULL;
  }

  /**
   * Get the localized DataResource.
   */
  public function get($identifier, $version = NULL, $perpective = self::LOCAL_FILE_PERSPECTIVE): ?DataResource {
    $resource = $this->getResourceSource($identifier, $version);

    if (!$resource) {
      return NULL;
    }

    $ff = $this->getFileFetcher($resource);

    if ($ff->getResult()->getStatus() != Result::DONE) {
      return NULL;
    }

    $this->registerNewPerspectives($resource, $ff);

    return $this->resourceMapper->get($resource->getIdentifier(), $perpective, $resource->getVersion());
  }

  /**
   * Private.
   */
  private function registerNewPerspectives(DataResource $resource, FileFetcher $fileFetcher) {

    $localFilePath = $fileFetcher->getStateProperty('destination');
    $public_dir = 'file://' . $this->drupalFiles->getPublicFilesDirectory();
    $localFileDrupalUri = str_replace($public_dir, 'public://', $localFilePath);
    $localUrl = $this->drupalFiles->fileCreateUrl($localFileDrupalUri);
    $localUrl = Referencer::hostify($localUrl);

    $new = $resource->createNewPerspective(self::LOCAL_FILE_PERSPECTIVE, $localFilePath);

    try {
      $this->resourceMapper->registerNewPerspective($new);
    }
    catch (AlreadyRegistered $e) {
    }

    $localUrlPerspective = $resource->createNewPerspective(self::LOCAL_URL_PERSPECTIVE, $localUrl);

    try {
      $this->resourceMapper->registerNewPerspective($localUrlPerspective);
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
   * Remove local file.
   */
  public function remove($identifier, $version = NULL) {
    $resource = $this->get($identifier, $version);
    $resource2 = $this->get($identifier, $version, self::LOCAL_URL_PERSPECTIVE);
    if ($resource2) {
      $this->resourceMapper->remove($resource2);
    }
    if ($resource) {
      $uuid = $resource->getUniqueIdentifierNoPerspective();
      if (file_exists($resource->getFilePath())) {
        $this->drupalFiles->getFilesystem()
          ->deleteRecursive($this->getPublicLocalizedDirectory($resource));
      }
      $this->removeJob($uuid);
      $this->resourceMapper->remove($resource);
    }
  }

  /**
   * Remove the filefetcher job record.
   */
  private function removeJob($uuid) {
    if ($uuid) {
      $this->jobStoreFactory->getInstance(FileFetcher::class)->remove($uuid);
    }
  }

  /**
   * Private.
   */
  private function getResourceSource($identifier, $version = NULL): ?DataResource {
    return $this->resourceMapper->get($identifier, DataResource::DEFAULT_SOURCE_PERSPECTIVE, $version);
  }

  /**
   * Get a FileFetcher object to copy the file from source to local.
   *
   * @param \Drupal\common\DataResource $dataResource
   *   Data resource object we want to process.
   *
   * @return \FileFetcher\FileFetcher
   *   FileFetcher object which is ready to transfer the file.
   */
  public function getFileFetcher(DataResource $dataResource): FileFetcher {
    return $this->fileFetcherFactory->getInstance(
      $dataResource->getUniqueIdentifierNoPerspective(),
      [
        'filePath' => UrlHostTokenResolver::resolveFilePath($dataResource->getFilePath()),
        'temporaryDirectory' => $this->getPublicLocalizedDirectory($dataResource),
      ]
    );
  }

  /**
   * Get the prepared directory path to the localized destination.
   *
   * Will attempt to create the path.
   *
   * @param \Drupal\common\DataResource $dataResource
   *   DataResource object to represent.
   * @param string $public_path
   *   Path within the public:// scheme where this resource will eventually be
   *   created. Defaults to 'resource/'.
   *
   * @return string
   *   Public scheme URI to the directory.
   */
  protected function getPublicLocalizedDirectory(DataResource $dataResource, string $public_path = 'resources/'): string {
    $uri = 'public://' . $public_path . $dataResource->getUniqueIdentifierNoPerspective();
    $this->getFilesystem()
      ->prepareDirectory($uri, FileSystemInterface::CREATE_DIRECTORY);
    return $uri;
  }

  /**
   * Get the Drupal filesystem service.
   *
   * @return \Drupal\Core\File\FileSystemInterface
   *   Drupal filesystem.
   */
  public function getFileSystem(): FileSystemInterface {
    return $this->drupalFiles->getFileSystem();
  }

}
