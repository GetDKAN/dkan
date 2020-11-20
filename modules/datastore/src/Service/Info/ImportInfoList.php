<?php

namespace Drupal\datastore\Service\Info;

use Drupal\common\Storage\JobStoreFactory;
use FileFetcher\FileFetcher;

/**
 * Definition of an "importer list" that allows for reporting.
 */
class ImportInfoList {

  /**
   * A JobStore object.
   *
   * @var \Drupal\common\Storage\JobStore
   */
  private $jobStoreFactory;

  private $importInfo;

  /**
   * Constructor.
   */
  public function __construct(JobStoreFactory $jobStoreFactory, ImportInfo $importInfo) {
    $this->jobStoreFactory = $jobStoreFactory;
    $this->importInfo = $importInfo;
  }

  /**
   * Retrieve stored jobs and build the list array property.
   *
   * @return array
   *   An array of ImportInfo objects, keyed by UUID.
   *
   * @todo Going directly to get filefetcher objects does not feel right.
   * We should have cleaner interfaces to get the data we need.
   */
  public function buildList() {
    $list = [];

    $store = $this->jobStoreFactory->getInstance(FileFetcher::class);

    foreach ($store->retrieveAll() as $id) {
      $pieces = explode("_", $id);

      // The filefetcher identifier for resources has the form <id>_<version>
      // by doing this check we can eliminate processing some unrelated file
      // fetching activities, but we should @todo better.
      if (count($pieces) == 2) {
        $list[$id] = $this->importInfo->getItem($pieces[0], $pieces[1]);
      }
    }

    return $list;
  }

}
