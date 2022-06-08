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
   * Import a datastore.
   *
   * @param string $identifier
   *   The uuid of a resource.
   * @param bool $deferred
   *   Whether or not the process should be deferred to a queue.
   *
   * @todo pass configurable options for csv delimiter, quite, and escape characters.
   * @command dkan:datastore:import
   */
  public function import($identifier, $deferred = FALSE) {

    try {
      $result = $this->datastoreService->import($identifier, $deferred);
      $status = $result['Import']->getStatus();
      $this->logger->notice("Ran import for {$identifier}; status: $status");
    }
    catch (\Exception $e) {
      $this->logger->error("No resource found to import with identifier {$identifier}");
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
   * @param string $identifier
   *   Datastore resource identifier, e.g., "b210fb966b5f68be0421b928631e5d51".
   *
   * @option keep-local
   *   Do not remove localized resource, only datastore.
   *
   * @command dkan:datastore:drop
   */
  public function drop(string $identifier, array $options = ['keep-local' => FALSE]) {
    $keep_local = $options['keep-local'] ? FALSE : TRUE;
    $this->datastoreService->drop($identifier, NULL, $keep_local);
    $this->logger->notice("Successfully dropped the datastore for resource {$identifier}");
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
