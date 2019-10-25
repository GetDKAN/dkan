<?php

namespace Drupal\dkan_datastore\Service\ImporterList;

use Drupal\dkan_datastore\Service\Factory\Import;
use Drupal\dkan_datastore\Service\Factory\Resource;
use Drupal\dkan_datastore\Storage\JobStoreFactory;
use FileFetcher\FileFetcher;

/**
 * Definition of an "importer list" that allows for reporting.
 */
class ImporterList {

  /**
   * A JobStore object.
   *
   * @var \Drupal\dkan_datastore\Storage\JobStore
   */
  private $jobStoreFactory;

  private $resourceServiceFactory;
  private $importServiceFactory;

  /**
   * Constructor.
   */
  public function __construct(JobStoreFactory $jobStoreFactory, Resource $resrouceServiceFactory, Import $importServiceFactory) {
    $this->jobStoreFactory = $jobStoreFactory;
    $this->resourceServiceFactory = $resrouceServiceFactory;
    $this->importServiceFactory = $importServiceFactory;
  }

  /**
   * Retrieve stored jobs and build the list array property.
   *
   * @return array
   *   An array of ImporterListItem objects, keyed by UUID.
   */
  private function buildList() {
    $list = [];

    $fileFetchers = [];
    $importers = [];

    $store = $this->jobStoreFactory->getInstance(FileFetcher::class);
    foreach ($store->retrieveAll() as $id) {
      $fileFetchers[$id] = $this->resourceServiceFactory->getInstance($id)->getFileFetcher();

      /* @var $resource \Dkan\Datastore\Resource */
      $resource = $this->resourceServiceFactory->getInstance($id)->get();
      $importers[$id] = $this->importServiceFactory->getInstance($resource->getId(), ['resource' => $resource])->getImporter();
    }

    foreach ($fileFetchers as $uuid => $fileFetcher) {
      $importer = isset($importers[$uuid]) ? $importers[$uuid] : NULL;
      $list[$uuid] = ImporterListItem::getItem($fileFetcher, $importer);
    }

    return $list;
  }

  /**
   * Static function to allow easy creation of lists.
   */
  public static function getList(JobStoreFactory $jobStoreFactory, Resource $resrouceServiceFactory, Import $importServiceFactory): array {
    $importerLister = new ImporterList($jobStoreFactory, $resrouceServiceFactory, $importServiceFactory);
    return $importerLister->buildList();
  }

}
