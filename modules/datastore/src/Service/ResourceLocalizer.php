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
   * Local file perspective key.
   *
   * @var string
   */
  const LOCAL_FILE_PERSPECTIVE = 'local_file';

  /**
   * Local URL perspective key.
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
   * @var \Contracts\FactoryInterface
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
   * Retrieve the file and create a local copy of it.
   */
  public function localize($identifier, $version = NULL): ?Result {
    $resource = $this->getResourceSource($identifier, $version);
    if ($resource) {
      $ff = $this->getFileFetcher($resource);
      return $ff->run();
    }
    return NULL;
  }

  /**
   * Get the localized resource.
   */
  public function get($identifier, $version = NULL, $perpective = self::LOCAL_FILE_PERSPECTIVE): ?DataResource {
    $resource = $this->getResourceSource($identifier, $version);

    if (!$resource) {
      return NULL;
    }

    $ff = $this->getFileFetcher($resource);
    $status = $ff->getResult()->getStatus();

    if ($status != Result::DONE) {
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
    $dir = 'file://' . $this->drupalFiles->getPublicFilesDirectory();
    $localFileDrupalUri = str_replace($dir, 'public://', $localFilePath);
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
      $uuid = $this->getUniqueIdentifierForDataResource($resource);
      if (file_exists($resource->getFilePath())) {
        $this->drupalFiles->getFilesystem()
          ->deleteRecursive('public://resources/' . $uuid);
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
   * Get FileFetcher.
   */
  public function getFileFetcher(DataResource $resource): FileFetcher {
    $uuid = $this->getUniqueIdentifierForDataResource($resource);
    $directory = 'public://resources/' . $uuid;
    $this->getFilesystem()
      ->prepareDirectory($directory, FileSystemInterface::CREATE_DIRECTORY);
    $config = [
      'filePath' => UrlHostTokenResolver::resolveFilePath($resource->getFilePath()),
      'temporaryDirectory' => $directory,
    ];
    return $this->fileFetcherFactory->getInstance($uuid, $config);
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

  protected function getUniqueIdentifierForDataResource(DataResource $dataResource): string {
    return $dataResource->getIdentifier() . '_' . $dataResource->getVersion();
  }

}
