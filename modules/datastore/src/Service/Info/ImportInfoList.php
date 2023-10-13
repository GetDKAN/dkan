<?php

namespace Drupal\datastore\Service\Info;

use Drupal\common\FileFetcher\FileFetcherFactory;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use FileFetcher\FileFetcher;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Definition of an "importer list" that allows for reporting.
 */
class ImportInfoList implements ContainerInjectionInterface {

  /**
   * A JobStore object.
   *
   * @var \Drupal\common\FileFetcher\FileFetcherFactory
   */
  private FileFetcherFactory $fileFetcherFactory;

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
      $container->get('dkan.common.file_fetcher'),
      $container->get('dkan.datastore.import_info')
    );
  }

  /**
   * Constructor.
   */
  public function __construct(FileFetcherFactory $fileFetcherFactory, ImportInfo $importInfo) {
    $this->fileFetcherFactory = $fileFetcherFactory;
    $this->importInfo = $importInfo;
  }

  /**
   * Retrieve stored jobs and build the list array property.
   *
   * @return array
   *   An array of ImportInfo objects, keyed by UUID.
   */
  public function buildList() {
    $list = [];

    $store = $this->fileFetcherFactory->getReadOnlyInstance(FileFetcher::class);

    foreach ($store->retrieveAll() as $id) {
      $pieces = explode('_', $id);

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
