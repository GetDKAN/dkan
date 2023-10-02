<?php

namespace Drupal\datastore\Service\Info;

use Drupal\common\DataResource;
use Drupal\datastore\Plugin\QueueWorker\ImportJob;
use Drupal\common\Storage\JobStoreFactory;
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
   * A JobStore object.
   *
   * @var \Drupal\common\Storage\JobStoreFactory
   */
  private $jobStoreFactory;

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
   * FileFetcher service.
   *
   * @var \FileFetcher\FileFetcher
   */
  private $fileFetcher;

  /**
   * Resourcer mapper service.
   *
   * @var \Drupal\metastore\ResourceMapper
   */
  private ResourceMapper $resourceMapper;

  /**
   * Constructor.
   */
  public function __construct(
    JobStoreFactory $jobStoreFactory,
    ResourceLocalizer $resourceLocalizer,
    ImportFactoryInterface $importServiceFactory,
    ResourceMapper $resourceMapper
  ) {
    $this->jobStoreFactory = $jobStoreFactory;
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
    $ff = NULL;
    $imp = NULL;
    // Get the record from the mapper. ResourceLocalizer->get() has side
    // effects that we don't want.
    $resource = $this->resourceMapper->get($identifier, DataResource::DEFAULT_SOURCE_PERSPECTIVE, $version);

    if ($resource) {
      $ff = $this->resourceLocalizer->getFileFetcher($resource);

      $imp = $this->importServiceFactory->getInstance(
        $resource->getUniqueIdentifier(),
        ['resource' => $resource]
      )->getImporter();
    }

    $item = (object) [
      'fileName' => '',
      'fileFetcherStatus' => 'waiting',
      'fileFetcherBytes' => 0,
      'fileFetcherPercentDone' => 0,
      'importerStatus' => 'waiting',
      'importerBytes' => 0,
      'importerPercentDone' => 0,
      'importerError' => NULL,
    ];

    if (isset($ff)) {
      $this->fileFetcher = $ff;
      $item->fileName = $this->getFileName($ff);
      $item->fileFetcherStatus = $ff->getResult()->getStatus();
      $item->fileFetcherBytes = $this->getBytesProcessed($ff);
      $item->fileFetcherPercentDone = $this->getPercentDone($ff);
    }

    /** @var \Drupal\datastore\Plugin\QueueWorker\ImportJob $imp */
    if (isset($imp)) {
      $item->importerStatus = $imp->getResult()->getStatus();
      $item->importerError = $imp->getResult()->getError();
      $item->importerBytes = $this->getBytesProcessed($imp);
      $item->importerPercentDone = $this->getPercentDone($imp);
    }

    return (object) $item;
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
    $filesize = $this->getFileSize();
    return ($filesize > 0) ? round($bytes / $filesize * 100) : 0;
  }

  /**
   * Get the filesize for the resource file.
   *
   * @return int
   *   File size in bytes.
   */
  protected function getFileSize(): int {
    if (isset($this->fileFetcher)) {
      return $this->fileFetcher->getStateProperty('total_bytes');
    }
    return 0;
  }

  /**
   * Calculate bytes processed based on chunks processed in the importer data.
   *
   * @param \Procrastinator\Job\Job $job
   *   Either a FileFetcher or Importer object.
   *
   * @return int
   *   Total bytes processed.
   */
  protected function getBytesProcessed(Job $job): int {
    $className = get_class($job);
    switch ($className) {
      // For Importer, avoid going above total size due to chunk multiplication.
      case ImportJob::class:
        $chunksSize = $job->getStateProperty('chunksProcessed') * ImportJob::BYTES_PER_CHUNK;
        $fileSize = $this->getFileSize();
        return ($chunksSize > $fileSize) ? $fileSize : $chunksSize;

      case FileFetcher::class:
        return $job->getStateProperty('total_bytes_copied');

      default:
        return 0;
    }
  }

}
