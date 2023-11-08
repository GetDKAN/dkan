<?php

namespace Drupal\datastore\Service\Info;

use Drupal\common\DataResource;
use Drupal\datastore\Plugin\QueueWorker\ImportJob;
use Drupal\datastore\Service\Factory\ImportFactoryInterface;
use Drupal\datastore\Service\ResourceLocalizer;
use Drupal\metastore\ResourceMapper;
use FileFetcher\FileFetcher;
use Procrastinator\Job\Job;

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
   * @var \Drupal\metastore\ResourceMapper
   */
  private ResourceMapper $resourceMapper;

  protected static $defaultItemValues = [
    'fileName' => '',
    'fileFetcherStatus' => 'waiting',
    'fileFetcherBytes' => 0,
    'fileFetcherPercentDone' => 0,
    'importerStatus' => 'waiting',
    'importerBytes' => 0,
    'importerPercentDone' => 0,
    'importerError' => NULL,
  ];

  /**
   * Constructor.
   */
  public function __construct(
    ResourceLocalizer $resourceLocalizer,
    ImportFactoryInterface $importServiceFactory,
    ResourceMapper $resourceMapper
  ) {
    $this->resourceLocalizer = $resourceLocalizer;
    $this->importServiceFactory = $importServiceFactory;
    $this->resourceMapper = $resourceMapper;
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
   *   And object with info about imports: file name, fetching status, etc.
   */
  public function getItem(string $identifier, string $version) {
    $item = (object) static::$defaultItemValues;

    if ($resource = $this->resourceMapper->get($identifier, DataResource::DEFAULT_SOURCE_PERSPECTIVE, $version)) {
      /** @var \FileFetcher\FileFetcher $ff */
      if ($ff = $this->getFileFetcher($resource)) {
        $item->fileName = $this->getFileName($ff);
        $item->fileFetcherStatus = $ff->getResult()->getStatus();
        $item->fileFetcherBytes = $this->getBytesProcessed($ff);
        $item->fileFetcherPercentDone = $this->getPercentDone($ff);
      }

      /** @var \Drupal\datastore\Plugin\QueueWorker\ImportJob $imp */
      if ($imp = $this->getImporter($resource)) {
        $item->importerStatus = $imp->getResult()->getStatus();
        $item->importerError = $imp->getResult()->getError();
        $item->importerBytes = $this->getBytesProcessed($imp);
        $item->importerPercentDone = $this->getPercentDone($imp);
      }
    }

    return $item;
  }

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
    return $this->importServiceFactory->getInstance(
      $resource->getUniqueIdentifier(),
      ['resource' => $resource]
    )->getImporter();
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
   * @return float
   *   Percentage.
   */
  private function getPercentDone(Job $job): float {
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
