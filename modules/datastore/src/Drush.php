<?php

namespace Drupal\datastore;

use Consolidation\OutputFormatters\StructuredData\RowsOfFields;
use Consolidation\OutputFormatters\StructuredData\UnstructuredListData;
use Drupal\datastore\Service\ResourceLocalizer;
use Drupal\datastore\Service as Datastore;
use Drupal\metastore\Service as Metastore;
use Drush\Commands\DrushCommands;

/**
 * Drush commands for controlling the datastore.
 *
 * @codeCoverageIgnore
 */
class Drush extends DrushCommands {
  use TableTrait;
  /**
   * The metastore service.
   *
   * @var \Drupal\metastore\Service
   */
  protected $metastoreService;

  /**
   * The datastore service.
   *
   * @var \Drupal\datastore\Service
   */
  protected $datastoreService;

  /**
   * Resource localizer for handling remote resource URLs.
   *
   * @var \Drupal\datastore\Service\ResourceLocalizer
   */
  private $resourceLocalizer;

  /**
   * Constructor for DkanDatastoreCommands.
   */
  public function __construct(
    Metastore $metastoreService,
    Datastore $datastoreService,
    ResourceLocalizer $resourceLocalizer
  ) {
    $this->metastoreService = $metastoreService;
    $this->datastoreService = $datastoreService;
    $this->resourceLocalizer = $resourceLocalizer;
  }

  /**
   * Private.
   */
  private function createRow($uuid, $item) {
    return [
      'uuid' => $uuid,
      'fileName' => $item->fileName,
      'fileFetcherStatus' => $item->fileFetcherStatus,
      'fileFetcherBytes' => \format_size($item->fileFetcherBytes) . " ($item->fileFetcherPercentDone%)",
      'importerStatus' => $item->importerStatus,
      'importerBytes' => \format_size($item->importerBytes) . " ($item->importerPercentDone%)",
    ];
  }

  /**
   * Drop a ALL datastore tables.
   *
   * @command dkan:datastore:drop-all
   */
  public function dropAll() {
    foreach ($this->metastoreService->getAll('distribution') as $distribution) {
      $uuid = $distribution->data->{"%Ref:downloadURL"}[0]->data->identifier;
      $this->drop($uuid);
    }
  }

}
