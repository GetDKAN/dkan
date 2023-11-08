<?php

namespace Drupal\datastore\Service\Info;

use Drupal\common\DataResource;
use Drupal\datastore\Plugin\QueueWorker\ImportJob;
use Drupal\common\Storage\JobStoreFactory;
use Drupal\datastore\Service\Factory\ImportFactoryInterface;
use Drupal\datastore\Service\Factory\ImportServiceFactory;
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
    [$ff, $imp] = $this->getFileFetcherAndImporter($identifier, $version);
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
    if ($ff) {
      $item->fileName = $this->getFileName($ff);
      $item->fileFetcherStatus = $ff->getResult()->getStatus();
      $item->fileFetcherBytes = $this->getBytesProcessed($ff);
      $item->fileFetcherPercentDone = $this->getPercentDone($ff);
    }
    if ($imp) {
      $item->importerStatus = $imp->getResult()->getStatus();
      $item->importerError = $imp->getResult()->getError();
      $item->importerBytes = $this->getBytesProcessed($imp);
      $item->importerPercentDone = $this->getPercentDone($imp);
    }
    return $item;
  }

  /**
   * Get the filefetcher and importer objects for a resource.
   *
   * @param string $identifier
   *   Resource identifier.
   * @param string $version
   *   Resource version.
   *
   * @return array
   *   Array with a filefetcher and importer object.
   *
   * @todo place this inline to avoid the awkward return array.
   */
  protected function getFileFetcherAndImporter($identifier, $version) {
    try {
      // Use resource mapper rather than resource localizer, because
      // ResourceLocalizer::get() has side effects we don't want.
      $resource = $this->resourceMapper->get($identifier, DataResource::DEFAULT_SOURCE_PERSPECTIVE, $version);

      if ($resource) {
        $fileFetcher = $this->resourceLocalizer->getFileFetcher($resource);

        $importer = $this->importServiceFactory->getInstance(
          $resource->getUniqueIdentifier(),
          ['resource' => $resource]
        )->getImporter();

        return [$fileFetcher, $importer];
      }
    }
    catch (\Exception $e) {
    }
    return [NULL, NULL];
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
   *   Either a FileFetcher or Importer object.
   *
   * @return int
   *   Total bytes processed.
   */
  protected function getBytesProcessed(Job $job): int {
    // Handle ImportJob and its subclasses.
    if (is_a($job, ImportJob::class)) {
      // For Importer, avoid going above total size due to chunk multiplication.
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
