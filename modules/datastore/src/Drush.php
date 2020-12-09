<?php

namespace Drupal\datastore;

use Consolidation\OutputFormatters\StructuredData\RowsOfFields;
use Consolidation\OutputFormatters\StructuredData\UnstructuredListData;
use Drush\Commands\DrushCommands;

/**
 * Drush commands for controlling the datastore.
 *
 * @codeCoverageIgnore
 */
class Drush extends DrushCommands {

  /**
   * The datastore service.
   *
   * @var \Drupal\datastore\Service
   */
  protected $datastoreService;

  /**
   * Constructor for DkanDatastoreCommands.
   */
  public function __construct() {
    $this->datastoreService = \Drupal::service('datastore.service');
  }

  /**
   * Import a datastore.
   *
   * @param string $uuid
   *   The uuid of a resource.
   * @param bool $deferred
   *   Whether or not the process should be deferred to a queue.
   *
   * @TODO pass configurable options for csv delimiter, quite, and escape characters.
   * @command dkan:datastore:import
   * @aliases dkan-datastore:import
   * @deprecated dkan-datastore:import is deprecated and will be removed in a future Dkan release. Use dkan:datastore:import instead.
   */
  public function import($uuid, $deferred = FALSE) {

    try {
      $this->datastoreService->import($uuid, $deferred);
    }
    catch (\Exception $e) {
      $this->logger->error("We were not able to load the entity with uuid {$uuid}");
      $this->logger->debug($e->getMessage());
    }
  }

  /**
   * List information about all datastores.
   *
   * @field-labels
   *   uuid: Resource UUID
   *   fileName: File Name
   *   fileFetcherStatus: FileFetcher
   *   fileFetcherBytes: Processed
   *   importerStatus: Importer
   *   importerBytes: Processed
   *
   * @options format The format of the data.
   * @options status Show imports of the given status.
   * @options uuid-only Only the list of uuids.
   *
   * @command dkan:datastore:list
   * @aliases dkan-datastore:list
   * @deprecated dkan-datastore:list is deprecated and will be removed in a future Dkan release. Use dkan:datastore:list instead.
   */
  public function list($options = [
    'format' => 'table',
    'status' => NULL,
    'uuid-only' => FALSE,
  ]) {
    $status = $options['status'];
    $uuid_only = $options['uuid-only'];

    $list = $this->datastoreService->list();
    $rows = [];
    foreach ($list as $uuid => $item) {
      $rows[] = $this->createRow($uuid, $item);
    }

    if (!empty($status)) {
      $rows = array_filter($rows, function ($row) use ($status) {
        if ($row['fileFetcherStatus'] == $status || $row['importerStatus'] == $status) {
          return TRUE;
        }
        return FALSE;
      });
    }

    if ($uuid_only) {
      foreach ($rows as $index => $row) {
        $rows[$index] = $row['uuid'];
      }
      return new UnstructuredListData($rows);
    }

    return new RowsOfFields($rows);
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
   * Drop a datastore.
   *
   * @param string $uuid
   *   The uuid of a dataset.
   *
   * @command dkan:datastore:drop
   * @aliases dkan-datastore:drop
   * @deprecated dkan-datastore:drop is deprecated and will be removed in a future Dkan release. Use dkan:datastore:drop instead.
   */
  public function drop($uuid) {
    try {
      $this->datastoreService->drop($uuid);
    }
    catch (\Exception $e) {
      $this->logger->error("We were not able to load the entity with uuid {$uuid}");
      $this->logger->debug($e->getMessage());
    }
  }

}
