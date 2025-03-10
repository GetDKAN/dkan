<?php

namespace Drupal\datastore\Service\Info;

use Drupal\common\DataResource;
use Drupal\datastore\DatastoreService;
use Drupal\datastore\Plugin\QueueWorker\ImportJob;
use Drupal\datastore\Service\Factory\ImportFactoryInterface;
use Drupal\datastore\Service\ResourceLocalizer;
use Drupal\metastore\ResourceMapper;
use FileFetcher\FileFetcher;
use Procrastinator\Job\Job;
use Procrastinator\Result;

/**
 * Defines and provide a single item for an ImportInfoList.
 */
class ImportInfo {

  /**
   * Resource localizer service.
   *
   * @var \Drupal\datastore\Service\ResourceLocalizer
   */
  private $resourceLocalizer;

  /**
   * Import factory service.
   *
   * @var \Drupal\datastore\Service\Factory\ImportFactoryInterface
   */
  private $importServiceFactory;

  /**
   * Resource mapper service.
   *
   * @var \Drupal\metastore\ResourceMapper
   */
  private ResourceMapper $resourceMapper;

  /**
   * Default values for import status.
   *
   * @var array
   */
  protected static $defaultItemValues = [
    'fileName' => '',
    'fileFetcherStatus' => Result::WAITING,
    'fileFetcherBytes' => 0,
    'fileFetcherPercentDone' => 0,
    'importerStatus' => Result::WAITING,
    'importerBytes' => 0,
    'importerPercentDone' => 0,
    'importerError' => NULL,
  ];

  /**
   * Datastore service.
   *
   * @var \Drupal\datastore\DatastoreService
   */
  protected DatastoreService $datastoreService;

  /**
   * Constructor.
   */
  public function __construct(
    ResourceLocalizer $resourceLocalizer,
    ImportFactoryInterface $importServiceFactory,
    ResourceMapper $resourceMapper,
    DatastoreService $datastoreService,
  ) {
    $this->resourceLocalizer = $resourceLocalizer;
    $this->importServiceFactory = $importServiceFactory;
    $this->resourceMapper = $resourceMapper;
    $this->datastoreService = $datastoreService;
  }

  /**
   * Get an information item.
   *
   * @param string $identifier
   *   Resource identifier.
   * @param string $version
   *   Resource version.
   *
   * @return object
   *   An object with info about imports: file name, fetching status, etc.
   */
  public function getItem(string $identifier, string $version) {
    $item = (object) static::$defaultItemValues;

    if ($resource = $this->resourceMapper->get($identifier, ResourceLocalizer::LOCAL_FILE_PERSPECTIVE, $version)) {
      /** @var \FileFetcher\FileFetcher $ff */
      if ($ff = $this->getFileFetcher($resource)) {
        $item->fileName = $this->getFileName($ff);
        $item->fileFetcherStatus = $ff->getResult()->getStatus();
        $item->fileFetcherBytes = $this->getBytesProcessed($ff);
        $item->fileFetcherPercentDone = $this->getPercentDone($ff);
      }

      /** @var \Drupal\datastore\Plugin\QueueWorker\ImportJob $import_job */
      if ($import_job = $this->getImporter($resource)) {
        $item->importerStatus = $import_job->getResult()->getStatus();
        $item->importerError = $import_job->getResult()->getError();
        $item->importerBytes = $this->getBytesProcessed($import_job);
        $item->importerPercentDone = $this->getPercentDone($import_job);
      }
    }

    return $item;
  }

  /**
   * Get a file fetcher for the given resource.
   *
   * @param \Drupal\common\DataResource $resource
   *   Resource to get the file fetcher for.
   *
   * @return \FileFetcher\FileFetcher
   *   File fetcher object for the resource.
   */
  protected function getFileFetcher(DataResource $resource): FileFetcher {
    return $this->resourceLocalizer->getFileFetcher($resource);
  }

  /**
   * Get an import job store object for the resource.
   *
   * @param \Drupal\common\DataResource $resource
   *   Resource object reperesenting the resource.
   *
   * @return \Drupal\datastore\Plugin\QueueWorker\ImportJob
   *   Import
   */
  protected function getImporter(DataResource $resource): ImportJob {
    return $this->datastoreService->getImportService($resource)->getImporter();
  }

  /**
   * Using the fileFetcher object, find the file path and extract the name.
   */
  private function getFileName($fileFetcher): string {
    $fileLocation = $fileFetcher->getStateProperty('source');
    $locationParts = explode('/', $fileLocation);
    return end($locationParts);
  }

  /**
   * Get a percentage of the total file procesed for either job type.
   *
   * @param \Procrastinator\Job\Job $job
   *   Either a FileFetcher or Importer object.
   *
   * @return float|null
   *   Percentage.
   */
  private function getPercentDone(Job $job): ?float {
    // If the job is done, but precent < 100, NULL.
    if ($job->getResult()->getStatus() == Result::DONE) {
      return 100;
    }
    $bytes = $this->getBytesProcessed($job);
    $filesize = $this->getFileSize($job);
    return ($filesize > 0) ? round($bytes / $filesize * 100) : 0;
  }

  /**
   * Get the filesize for the resource file.
   *
   * @return int
   *   File size in bytes.
   */
  protected function getFileSize(Job $job): int {
    return $job->getStateProperty('total_bytes');
  }

  /**
   * Calculate bytes processed based on chunks processed in the importer data.
   *
   * @param \Procrastinator\Job\Job $job
   *   Job object. In practice this will be either an ImportJob, a FileFetcher,
   *   or one of their subclasses.
   *
   * @return int
   *   Total bytes processed.
   */
  protected function getBytesProcessed(Job $job): int {
    // Handle ImportJob and its subclasses.
    if (is_a($job, ImportJob::class)) {
      $chunksSize = $job->getStateProperty('chunksProcessed') * ImportJob::BYTES_PER_CHUNK;
      $fileSize = $this->getFileSize($job);
      return ($chunksSize > $fileSize) ? $fileSize : $chunksSize;
    }
    // Handle FileFetcher and its subclasses.
    if (is_a($job, FileFetcher::class)) {
      return $job->getStateProperty('total_bytes_copied');
    }
    return 0;
  }

}
