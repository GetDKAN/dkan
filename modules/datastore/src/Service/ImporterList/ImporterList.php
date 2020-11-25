<?php

namespace Drupal\datastore\Service\ImporterList;

use Drupal\datastore\Service\Factory\Import;
use Drupal\common\Storage\JobStoreFactory;
use Drupal\datastore\Service\ResourceLocalizer;
use FileFetcher\FileFetcher;

/**
 * Definition of an "importer list" that allows for reporting.
 */
class ImporterList {

  /**
   * A JobStore object.
   *
   * @var \Drupal\common\Storage\JobStore
   */
  private $jobStoreFactory;

  private $resourceLocalizer;
  private $importServiceFactory;

  /**
   * Constructor.
   */
  public function __construct(JobStoreFactory $jobStoreFactory, ResourceLocalizer $resourceLocalizer, Import $importServiceFactory) {
    $this->jobStoreFactory = $jobStoreFactory;
    $this->resourceLocalizer = $resourceLocalizer;
    $this->importServiceFactory = $importServiceFactory;
  }

  /**
   * Retrieve stored jobs and build the list array property
   *
   * @return array
   *   An array of ImporterListItem objects, keyed by UUID.
   *
   * @todo Going directly to get filefetcher objects does not feel right.
   * We should have cleaner interfaces to get the data we need.
   */
  private function buildList() {
    $list = [];

    $fileFetchers = [];
    $importers = [];

    $store = $this->jobStoreFactory->getInstance(FileFetcher::class);
    foreach ($store->retrieveAll() as $id) {
      [$ff, $imp] = $this->getFileFetcherAndImporter($id);
      if (isset($ff) && isset($imp)) {
        $fileFetchers[$id] = $ff;
        $importers[$id] = $imp;
      }
    }

    foreach ($fileFetchers as $uuid => $fileFetcher) {
      $importer = isset($importers[$uuid]) ? $importers[$uuid] : NULL;
      $list[$uuid] = ImporterListItem::getItem($fileFetcher, $importer);
    }

    return $list;
  }

  /**
   * Private.
   */
  private function getFileFetcherAndImporter($id) {
    $pieces = explode("_", $id);
    try {
      $resource = $this->resourceLocalizer->get($pieces[0], $pieces[1]);
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
   * Static function to allow easy creation of lists.
   */
  public static function getList(JobStoreFactory $jobStoreFactory, ResourceLocalizer $resrouceLocalizer, Import $importServiceFactory): array {
    $importerLister = new ImporterList($jobStoreFactory, $resrouceLocalizer, $importServiceFactory);
    return $importerLister->buildList();
  }

}
