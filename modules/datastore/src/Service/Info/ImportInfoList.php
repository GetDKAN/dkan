<?php

namespace Drupal\datastore\Service\Info;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\common\Storage\FileFetcherJobStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Definition of an "importer list" that allows for reporting.
 */
class ImportInfoList implements ContainerInjectionInterface {

  /**
   * File fetcher job store factory.
   */
  private FileFetcherJobStoreFactory $fileFetcherJobStoreFactory;

  /**
   * Datastore import job info.
   *
   * @var \Drupal\datastore\Service\Info\ImportInfo
   */
  private $importInfo;

  /**
   * Create.
   *
   * @inheritdoc
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('dkan.common.filefetcher_job_store_factory'),
      $container->get('dkan.datastore.import_info')
    );
  }

  /**
   * Constructor.
   */
  public function __construct(FileFetcherJobStoreFactory $fileFetcherJobStoreFactory, ImportInfo $importInfo) {
    $this->fileFetcherJobStoreFactory = $fileFetcherJobStoreFactory;
    $this->importInfo = $importInfo;
  }

  /**
   * Retrieve stored jobs and build the list array property.
   *
   * @return array
   *   An array of ImportInfo objects, keyed by UUID.
   *
   * @todo This method assumes a 1:1 relationship between filefetcher job stores
   *   and status to report. This might not be the case.
   */
  public function buildList() {
    $list = [];

    $store = $this->fileFetcherJobStoreFactory->getInstance();

    foreach ($store->retrieveAll() as $id) {
      $pieces = explode('_', (string) $id);

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
