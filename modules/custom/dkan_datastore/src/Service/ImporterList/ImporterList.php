<?php

namespace Drupal\dkan_datastore\Service\ImporterList;

use Dkan\Datastore\Importer;
use FileFetcher\FileFetcher;
use Drupal\dkan_datastore\Storage\JobStore;

/**
 * Definition of an "importer list" that allows for reporting.
 */
class ImporterList {

  /**
   * A JobStore object.
   *
   * @var \Drupal\dkan_datastore\Storage\JobStore
   */
  private $jobStore;

  /**
   * Constructor function.
   *
   * @param \Drupal\dkan_datastore\Storage\JobStore $jobStore
   *   A JobStore object.
   */
  public function __construct(JobStore $jobStore) {
    $this->jobStore = $jobStore;
  }

  /**
   * Retrieve stored jobs and build the list array property.
   *
   * @return array
   *   An array of ImporterListItem objects, keyed by UUID.
   */
  private function buildList() {
    $fileFetchers = $this->jobStore->retrieveAll(FileFetcher::class);
    $importers = $this->jobStore->retrieveAll(Importer::class);
    $list = [];
    foreach ($fileFetchers as $uuid => $fileFetcher) {
      $importer = isset($importers[$uuid]) ? $importers[$uuid] : NULL;
      $list[$uuid] = ImporterListItem::getItem($fileFetcher, $importer);
    }
    return $list;
  }

  /**
   * Static function to allow easy creation of lists.
   *
   * @param \Drupal\dkan_datastore\Storage\JobStore $jobStore
   *   A jobstore object.
   */
  public static function getList(JobStore $jobStore): array {
    $importerLister = new ImporterList($jobStore);
    return $importerLister->buildList();
  }

}
