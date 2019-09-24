<?php

namespace Drupal\dkan_datastore\Service\ImporterList;

use FileFetcher\FileFetcher;
use Procrastinator\Job\Job;

/**
 * Defines and provide a single item for an ImporterList.
 */
class ImporterListItem {

  /**
   * FileFetcher object.
   *
   * @var \FileFetcher\FileFetcher
   */
  public $fileFetcher;

  /**
   * Datastore importer object.
   *
   * @var \Dkan\Datastore\Importer
   */
  public $importer;

  /**
   * File fetcher job result status code. See result class for code definitions.
   *
   * @var string
   * @see \Procrastinator\Result::STOPPED
   * @see \Procrastinator\Result::IN_PROGRESS
   * @see \Procrastinator\Result::ERROR
   * @see \Procrastinator\Result::DONE
   */
  public $fileFetcherStatus;

  /**
   * The percentage of the file that has been downloaded for import.
   *
   * @var float
   */
  public $fileFetcherPercentDone;

  /**
   * The number of bytes that have been downloaded for import.
   *
   * @var float
   */
  public $fileFetcherBytes;

  /**
   * File name (without path) for the resource.
   *
   * @var string
   */
  public $fileName;

  /**
   * Importer job result status code. See result class for code definitions.
   *
   * @var string
   * @see \Procrastinator\Result::STOPPED
   * @see \Procrastinator\Result::IN_PROGRESS
   * @see \Procrastinator\Result::ERROR
   * @see \Procrastinator\Result::DONE
   */
  public $importerStatus;

  /**
   * Number of bytes processed in file if importer has run.
   *
   * Note that this is calculated by multiplying the chunks by 32, which may
   * result in the total being slightly off.
   *
   * @var int
   */
  public $importerBytes;


  /**
   * The percentage of the file that has been parsed and imported.
   *
   * @var float
   */
  public $importerPercentDone;

  /**
   * Constructor method.
   *
   * @param \FileFetcher\FileFetcher $fileFetcher
   *   FileFetcher job object.
   * @param \Dkan\Datastore\Importer|null $importer
   *   Datastore importer job object, or NULL if one does not exist.
   */
  public function __construct(FileFetcher $fileFetcher, $importer = NULL) {
    $this->fileFetcher = $fileFetcher;
    $this->importer = $importer;
  }

  /**
   * Static function to build a full object with a single call.
   *
   * @param \FileFetcher\FileFetcher $fileFetcher
   *   The FileFetcher object.
   * @param \Dkan\Datastore\Importer|null $importer
   *   Importer object.
   */
  public static function getItem(FileFetcher $fileFetcher, $importer = NULL) {
    $item = new ImporterListItem($fileFetcher, $importer);
    $item->buildItem();
    return $item;
  }

  /**
   * Build out the full "item" object and set public properties.
   */
  private function buildItem() {
    $this->fileName = $this->getFileName();
    $this->fileFetcherStatus = $this->fileFetcher->getResult()->getStatus();
    $this->fileFetcherBytes = $this->getBytesProcessed($this->fileFetcher);
    $this->fileFetcherPercentDone = $this->getPercentDone($this->fileFetcher);
    $this->importerStatus = 'waiting';
    $this->importerBytes = 0;
    $this->importerPercentDone = 0;

    if (isset($this->importer)) {
      $this->importerStatus = $this->importer->getResult()->getStatus();
      $this->importerBytes = $this->getBytesProcessed($this->importer);
      $this->importerPercentDone = $this->getPercentDone($this->importer);
    }
  }

  /**
   * Using the fileFetcher object, find the file path and extract the name.
   */
  private function getFileName(): string {
    $fileLocation = $this->fileFetcher->getStateProperty('source');
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
    $jobShortClass = (new \ReflectionClass($job))->getShortName();
    switch ($jobShortClass) {
      // For Importer, avoid going above total size due to chunk multiplication.
      case 'Importer':
        $chunksSize = $job->getStateProperty('chunksProcessed') * 32;
        $fileSize = $this->getFileSize();
        return ($chunksSize > $fileSize) ? $fileSize : $chunksSize;

      case 'FileFetcher':
        return $job->getStateProperty('total_bytes_copied');
    }
  }

}
