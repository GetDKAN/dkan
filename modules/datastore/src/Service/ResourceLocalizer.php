<?php

namespace Drupal\datastore\Service;

use Drupal\common\DataResource;
use Drupal\common\EventDispatcherTrait;
use Drupal\common\FileFetcher\FileFetcherFactory;
use Drupal\common\LoggerTrait;
use Drupal\common\Storage\JobStoreFactory;
use Drupal\common\UrlHostTokenResolver;
use Drupal\common\Util\DrupalFiles;
use Drupal\Core\File\FileSystemInterface;
use Drupal\metastore\Exception\AlreadyRegistered;
use Drupal\metastore\ResourceMapper;
use FileFetcher\FileFetcher;
use Procrastinator\Result;

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
   *
   * Do not (yet) update the file map database with this information.
   */
  public function localize($identifier, $version = NULL): ?Result {
    if ($resource = $this->getResourceSource($identifier, $version)) {
      $ff = $this->getFileFetcher($resource);
      return $ff->run();
    }
    return NULL;
  }

  /**
   * Create local file and URL perspectives in the mapper, get a perspective.
   *
   * The source perspective must already exist.
   *
   * @return \Drupal\common\DataResource|null
   *   Return the perspective, or NULL if the source perspective did not exist.
   */
  public function get($identifier, $version = NULL, $perpective = self::LOCAL_FILE_PERSPECTIVE): ?DataResource {
    $resource = $this->getResourceSource($identifier, $version);

    if (!$resource) {
      return NULL;
    }

    $ff = $this->getFileFetcher($resource);

    if ($ff->getResult()->getStatus() !== Result::DONE) {
      return NULL;
    }

    $this->registerNewPerspectives($resource, $ff);

    return $this->resourceMapper->get($resource->getIdentifier(), $perpective, $resource->getVersion());
  }

  /**
   * Add local file and local URL perspectives to the resource mapper.
   */
  private function registerNewPerspectives(DataResource $resource, FileFetcher $fileFetcher) {

    $localFilePath = $fileFetcher->getStateProperty('destination');
    $public_dir = 'file://' . $this->drupalFiles->getPublicFilesDirectory();
    $localFileDrupalUri = str_replace($public_dir, 'public://', $localFilePath);
    $localUrl = $this->drupalFiles->fileCreateUrl($localFileDrupalUri);
    $localUrl = UrlHostTokenResolver::hostify($localUrl);

    $localFilePerspective = $resource->createNewPerspective(self::LOCAL_FILE_PERSPECTIVE, $localFilePath);

    try {
      $this->resourceMapper->registerNewPerspective($localFilePerspective);
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
   * Get a file fetcher result.
   */
  public function getResult($identifier, $version = NULL) {
    $ff = $this->getFileFetcher($this->getResourceSource($identifier, $version));
    return $ff->getResult();
  }

  /**
   * Remove local file.
   */
  public function remove($identifier, $version = NULL): void {
    if ($local_resource = $this->get($identifier, $version, self::LOCAL_URL_PERSPECTIVE)) {
      $this->resourceMapper->remove($local_resource);
    }
    if ($resource = $this->get($identifier, $version)) {
      $resource_id = $resource->getUniqueIdentifierNoPerspective();
      if (file_exists($resource->getFilePath())) {
        $this->drupalFiles->getFilesystem()
          ->deleteRecursive($this->getPublicLocalizedDirectory($resource));
      }
      $this->removeJob($resource_id);
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
   * Get a FileFetcher object for a source data resource, to copy to local.
   *
   * @param \Drupal\common\DataResource $sourceDataResource
   *   Data resource object we want to process. Assumed to be a source
   *   perspective.
   *
   * @return \FileFetcher\FileFetcher
   *   FileFetcher object which is ready to transfer the file.
   */
  public function getFileFetcher(DataResource $sourceDataResource): FileFetcher {
    return $this->fileFetcherFactory->getInstance(
      $sourceDataResource->getUniqueIdentifierNoPerspective(),
      [
        'filePath' => UrlHostTokenResolver::resolveFilePath($sourceDataResource->getFilePath()),
        'temporaryDirectory' => $this->getPublicLocalizedDirectory($sourceDataResource),
      ]
    );
  }

  /**
   * Resolve the source to the localized file path as a public URI.
   *
   * Note: The file fetcher also does this during the fetch.
   *
   * @param \Drupal\common\DataResource $source_resource
   *   Source DataResource.
   *
   * @return string
   *   Public URI for the temp localized file.
   *
   * @see \FileFetcher\Processor\ProcessorBase::getTemporaryFilePath()
   *
   * @todo Remove this from FileFetcher so concerns can be separated properly.
   */
  public function localizeFilePath(DataResource $source_resource): string {
    if ($source_resource->getPerspective() !== DataResource::DEFAULT_SOURCE_PERSPECTIVE) {
      throw new \InvalidArgumentException('DataResource must be source perspective.');
    }
    $public = $this->getPublicLocalizedDirectory($source_resource);
    return $public . '/' . basename($source_resource->getFilePath());
  }

  /**
   * Get the prepared directory path to the localized destination.
   *
   * Will attempt to create the path.
   *
   * @param \Drupal\common\DataResource $dataResource
   *   DataResource object to represent.
   * @param string $public_path
   *   Path within the public:// filesystem where this resource will eventually
   *   be created. Defaults to 'resource'.
   *
   * @return string
   *   Public scheme URI to the directory.
   *
   * @todo Create a config for $public_path.
   */
  public function getPublicLocalizedDirectory(DataResource $dataResource, string $public_path = 'resources'): string {
    $uri = 'public://' . $public_path . '/' . $dataResource->getUniqueIdentifierNoPerspective();
    $this->getFilesystem()
      ->prepareDirectory($uri, FileSystemInterface::CREATE_DIRECTORY);
    return $uri;
  }

  /**
   * Get the Drupal filesystem service.
   *
   * @return \Drupal\Core\File\FileSystemInterface
   *   Drupal filesystem.
   *
   * @todo Properly inject this service.
   */
  public function getFileSystem(): FileSystemInterface {
    return $this->drupalFiles->getFileSystem();
  }

}
