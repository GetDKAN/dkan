<?php

namespace Drupal\datastore\Service\Info;

use Dkan\Datastore\Importer;
use Drupal\common\Storage\JobStoreFactory;
use Drupal\datastore\Service\Factory\Import;
use Drupal\datastore\Service\ResourceLocalizer;
use FileFetcher\FileFetcher;
use Procrastinator\Job\Job;

/**
 * Defines and provide a single item for an ImportInfoList.
 */
class ImportInfo {

  /**
   * A JobStore object.
   *
   * @var \Drupal\common\Storage\JobStore
   */
  private $jobStoreFactory;

  private $resourceLocalizer;
  private $importServiceFactory;

  private $fileFetcher;

  /**
   * Constructor.
   */
  public function __construct(JobStoreFactory $jobStoreFactory, ResourceLocalizer $resourceLocalizer, Import $importServiceFactory) {
    $this->jobStoreFactory = $jobStoreFactory;
    $this->resourceLocalizer = $resourceLocalizer;
    $this->importServiceFactory = $importServiceFactory;
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
    $ff = NULL; $imp = NULL;
    [$ff, $imp] = $this->getFileFetcherAndImporter($identifier, $version);

    $item = (object) [
      'fileName' => '',
      'fileFetcherStatus' => 'waiting',
      'fileFetcherBytes' => 0,
      'fileFetcherPercentDone' => 0,
      'importerStatus' => 'waiting',
      'importerBytes' => 0,
      'importerPercentDone' => 0,
    ];

    if (isset($ff)) {
      $this->fileFetcher = $ff;
      $item->fileName = $this->getFileName($ff);
      $item->fileFetcherStatus = $ff->getResult()->getStatus();
      $item->fileFetcherBytes = $this->getBytesProcessed($ff);
      $item->fileFetcherPercentDone = $this->getPercentDone($ff);
    }

    /** @var \Dkan\Datastore\Importer $imp */
    if (isset($imp)) {
      $item->importerStatus = $imp->getResult()->getStatus();
      $item->importerBytes = $this->getBytesProcessed($imp);
      $item->importerPercentDone = $this->getPercentDone($imp);
    }

    return (object) $item;
  }

  /**
   * Private.
   */
  private function getFileFetcherAndImporter($identifier, $version) {
    try {
      $resource = $this->resourceLocalizer->get($identifier, $version);

      if ($resource) {
        $fileFetcher = $this->resourceLocalizer->getFileFetcher($resource);

        $importer = $this->importServiceFactory->getInstance($resource->getUniqueIdentifier(),
          ['resource' => $resource])->getImporter();

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
    $filesize = $this->getFileSize();
    return ($filesize > 0) ? round($bytes / $filesize * 100) : 0;
  }

  /**
   * Get the filesize for the resource file.
   *
   * @return int
   *   File size in bytes.
   */
  private function getFileSize(): int {
    return $this->fileFetcher->getStateProperty('total_bytes');
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
  private function getBytesProcessed(Job $job): int {
    $className = get_class($job);
    switch ($className) {
      // For Importer, avoid going above total size due to chunk multiplication.
      case Importer::class:
        $chunksSize = $job->getStateProperty('chunksProcessed') * Importer::BYTES_PER_CHUNK;
        $fileSize = $this->getFileSize();
        return ($chunksSize > $fileSize) ? $fileSize : $chunksSize;

      case FileFetcher::class:
        return $job->getStateProperty('total_bytes_copied');

      default:
        return 0;
    }
  }

}
