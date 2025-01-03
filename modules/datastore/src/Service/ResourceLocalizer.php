<?php

namespace Drupal\datastore\Service;

use Contracts\FactoryInterface;
use Drupal\common\DataResource;
use Drupal\common\EventDispatcherTrait;
use Drupal\common\UrlHostTokenResolver;
use Drupal\common\Util\DrupalFiles;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\common\Storage\FileFetcherJobStoreFactory;
use Drupal\metastore\Exception\AlreadyRegistered;
use Drupal\metastore\ResourceMapper;
use FileFetcher\FileFetcher;
use Procrastinator\Result;

/**
 * Resource localizer.
 */
class ResourceLocalizer {

  use EventDispatcherTrait;

  /**
   * Event sent when a resource is successfully localized.
   *
   * @var string
   */
  const EVENT_RESOURCE_LOCALIZED = 'event_resource_localized';

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
   */
  private ResourceMapper $resourceMapper;

  /**
   * DKAN resource file fetcher factory.
   *
   * @see \Drupal\common\FileFetcher\FileFetcherFactory
   */
  private FactoryInterface $fileFetcherFactory;

  /**
   * Drupal files utility service.
   */
  private DrupalFiles $drupalFiles;

  /**
   * File fetcher job store factory.
   */
  private FileFetcherJobStoreFactory $fileFetcherJobStoreFactory;

  /**
   * Drupal queue.
   */
  private QueueFactory $queueFactory;

  /**
   * Constructor.
   */
  public function __construct(
    ResourceMapper $fileMapper,
    FactoryInterface $fileFetcherFactory,
    DrupalFiles $drupalFiles,
    FileFetcherJobStoreFactory $fileFetcherJobStoreFactory,
    QueueFactory $queueFactory,
  ) {
    $this->resourceMapper = $fileMapper;
    $this->fileFetcherFactory = $fileFetcherFactory;
    $this->drupalFiles = $drupalFiles;
    $this->fileFetcherJobStoreFactory = $fileFetcherJobStoreFactory;
    $this->queueFactory = $queueFactory;
  }

  /**
   * Copy the source file to the local file system.
   *
   * As a side effect, register new perspectives to the mapper DB.
   */
  protected function localize($identifier, $version = NULL): Result {
    if ($resource = $this->getResourceSource($identifier, $version)) {
      $ff = $this->getFileFetcher($resource);
      $result = $ff->run();
      // The result object should report DONE, even if the file has previously
      // been localized.
      if ($result->getStatus() === Result::DONE) {
        // Localization is done. Register the perspectives.
        $this->registerNewPerspectives($resource, $ff->getStateProperty('destination'));
        // Send the event.
        $this->dispatchEvent(static::EVENT_RESOURCE_LOCALIZED, [
          'identifier' => $resource->getIdentifier(),
          'version' => $resource->getVersion(),
        ]);
      }
      return $result;
    }
    $result = new Result();
    $result->setStatus(Result::ERROR);
    $result->setError('Unable to find resource to localize: ' . $identifier . ':' . $version);
    return $result;
  }

  /**
   * Either localize or queue a localization.
   *
   * @param string $identifier
   *   Resource identifier.
   * @param string|null $version
   *   (Optional) Resource version. If not provided, will use latest revision.
   * @param bool $deferred
   *   (Optional) If TRUE, queue a localization task, otherwise perform the
   *   localization. Defaults to FALSE.
   *
   * @return \Procrastinator\Result
   *   Result of the process. If deferred, will be the result of creating a
   *   queue item. Otherwise, will be the result of localizing. Result will be
   *   DONE if the localization had already occurred.
   */
  public function localizeTask(string $identifier, ?string $version = NULL, bool $deferred = FALSE): Result {
    if (!$deferred) {
      return $this->localize($identifier, $version);
    }
    $result = new Result();
    if ($this->queueFactory->get('localize_import')->createItem([
      'identifier' => $identifier,
      'version' => $version,
    ]) !== FALSE) {
      $result->setStatus(Result::DONE);
      $result->setError('Queued localize_import for ' . $identifier . ':' . $version);
      return $result;
    }
    $result->setStatus(Result::ERROR);
    $result->setError('Failed to create localize_import queue for ' . $identifier . ':' . $version);
    return $result;
  }

  /**
   * Create local file and URL perspectives in the mapper, get a perspective.
   *
   * Requires the localized file to exist so it can be checksummed.
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

    if ($ff->getResult()->getStatus() != Result::DONE) {
      return NULL;
    }

    $this->registerNewPerspectives($resource, $ff->getStateProperty('destination'));

    return $this->resourceMapper->get($resource->getIdentifier(), $perpective, $resource->getVersion());
  }

  /**
   * Add local file and local URL perspectives to the resource mapper.
   */
  private function registerNewPerspectives(DataResource $resource, string $localFilePath) {
    $public_dir = 'file://' . $this->drupalFiles->getPublicFilesDirectory();
    $localFileDrupalUri = str_replace($public_dir, 'public://', $localFilePath);
    $localUrl = $this->drupalFiles->fileCreateUrl($localFileDrupalUri);
    $localUrl = UrlHostTokenResolver::hostify($localUrl);

    $new = $resource->createNewPerspective(self::LOCAL_FILE_PERSPECTIVE, $localFilePath);

    try {
      $this->resourceMapper->registerNewPerspective($new);
    }
    catch (AlreadyRegistered) {
    }

    $localUrlPerspective = $resource->createNewPerspective(self::LOCAL_URL_PERSPECTIVE, $localUrl);

    try {
      $this->resourceMapper->registerNewPerspective($localUrlPerspective);
    }
    catch (AlreadyRegistered) {
    }
  }

  /**
   * Remove local file.
   *
   * Also remove local perspectives from mapping DB.
   */
  public function remove($identifier, $version = NULL): void {
    // Remove the LOCAL_URL_PERSPECTIVE if it exists.
    if ($local_url_resource = $this->get($identifier, $version, self::LOCAL_URL_PERSPECTIVE)) {
      $this->resourceMapper->remove($local_url_resource);
    }
    // Remove the LOCAL_FILE_PERSPECTIVE if it exists.
    if ($resource = $this->get($identifier, $version, self::LOCAL_FILE_PERSPECTIVE)) {
      // Remove the file.
      if (file_exists($resource->getFilePath())) {
        $this->drupalFiles->getFilesystem()
          ->deleteRecursive($this->getPublicLocalizedDirectory($resource));
      }
      // Remove the fetcher job.
      $this->removeJob($resource->getUniqueIdentifierNoPerspective());
      // Remove the LOCAL_FILE_PERSPECTIVE.
      $this->resourceMapper->remove($resource);
    }
  }

  /**
   * Remove the filefetcher job record.
   */
  private function removeJob($uuid) {
    if ($uuid) {
      $this->fileFetcherJobStoreFactory->getInstance()->remove($uuid);
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

  /**
   * Prepare the local perspective for a resource.
   *
   * Will do the following:
   * - Prepare the directory in the file system.
   * - Add the local_url perspective to the resource mapper. Note this is
   *   missing the file checksum.
   * - Display the info necessary to perform an external file fetch.
   *
   * @param string $identifier
   *   Datastore resource identifier, e.g., "b210fb966b5f68be0421b928631e5d51".
   *
   * @return array
   *   Various localization paths.
   */
  public function prepareLocalized(string $identifier): array {
    $info = [];
    if ($resource = $this->resourceMapper->get($identifier)) {
      $public_dir = $this->getPublicLocalizedDirectory($resource);
      $localized_filepath = $this->localizeFilePath($resource);
      $localized_resource = $resource->createNewPerspective(
        ResourceLocalizer::LOCAL_FILE_PERSPECTIVE, $localized_filepath
      );
      try {
        $this->resourceMapper->registerNewPerspective($localized_resource);
      }
      catch (AlreadyRegistered) {
        // Catch the already-registered exception.
      }
      $file_system = $this->getFileSystem();
      $info = [
        'source' => $resource->getFilePath(),
        'path_uri' => $public_dir,
        'path' => $file_system->realpath($public_dir),
        'file_uri' => $localized_filepath,
        'file' => $file_system->realpath($localized_filepath),
      ];
    }
    return $info;
  }

}
