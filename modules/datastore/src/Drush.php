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
   * Import a datastore.
   *
   * @param string $uuid
   *   The uuid of a resource.
   * @param bool $deferred
   *   Whether or not the process should be deferred to a queue.
   *
   * @todo pass configurable options for csv delimiter, quite, and escape characters.
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
   *   The uuid of a dataset resource.
   *
   * @command dkan:datastore:drop
   * @aliases dkan-datastore:drop
   * @deprecated dkan-datastore:drop is deprecated and will be removed in a future Dkan release. Use dkan:datastore:drop instead.
   */
  public function drop($uuid) {
    try {
      // Retrieve the UUID for the dataset's resource before dropping the
      // dataset's resource mapper table entry.
      $resource = $this->resourceLocalizer->get($uuid);
      if (!isset($resource)) {
        throw new \UnexpectedValueException("Resource not found with uuid {$uuid}");
      }
      $resource_uuid = $resource->getUniqueIdentifier();
      // Drop the datastore table and corresponding resource mapper table entry.
      $this->datastoreService->drop($uuid);
      $this->logger->notice("Successfully dropped the datastore for {$uuid}");
    }
    catch (\Exception $e) {
      $this->logger->error("Unable to find an entity with uuid {$uuid}");
      $this->logger->debug($e->getMessage());
      return;
    }
    catch (\TypeError $e) {
      // This will catch all TypeErrors with all arguments. Since we only
      // pass the UUID to the DataStore::Service::drop method we can safely
      // assume the issue is with the uuid.
      $this->logger->error("Unexpected entity uuid.");
      $this->logger->debug($e->getMessage());
      return;
    }
    // Drop any remaining jobstore entries for this resource.
    $this->jobstorePrune($resource_uuid);
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
