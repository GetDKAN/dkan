<?php

namespace Drupal\dkan_datastore;

use Consolidation\OutputFormatters\StructuredData\RowsOfFields;
use Drupal\dkan_data\ValueReferencer;
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
   * @var \Drupal\dkan_datastore\Service\Service
   */
  protected $datastoreService;

  /**
   * Logger channel service.
   *
   * @var \Drupal\Core\Logger\LoggerChannel
   */
  protected $logger;

  /**
   * Constructor for DkanDatastoreCommands.
   */
  public function __construct() {
    $this->datastoreService = \Drupal::service('dkan_datastore.service');
    $this->logger = \Drupal::service('dkan_datastore.logger_channel');
  }

  /**
   * Import.
   *
   * @param string $uuid
   *   The uuid of a resource.
   * @param bool $deferred
   *   Whether or not the process should be deferred to a queue.
   *
   * @TODO pass configurable options for csv delimiter, quite, and escape characters.
   * @command dkan-datastore:import
   */
  public function import($uuid, $deferred = FALSE) {

    try {
      // Load metadata with both identifier and data for this request.
      drupal_static('dkan_data_dereference_method', ValueReferencer::DEREFERENCE_OUTPUT_VERBOSE);

      $this->datastoreService->import($uuid, $deferred);
    }
    catch (\Exception $e) {
      $this->logger->error("We were not able to load the entity with uuid {$uuid}");
      $this->logger->debug($e->getMessage());
    }
  }

  /**
   * List.
   *
   * @field-labels
   *   uuid: Resource UUID
   *   fileName: File Name
   *   fileFetcherStatus: FileFetcher
   *   fileFetcherBytes: Processed
   *   importerStatus: Importer
   *   importerBytes: Processed
   *
   * @return \Consolidation\OutputFormatters\StructuredData\RowsOfFields
   *   The importer list, organized into a RowsOfFields formatter object.
   *
   * @command dkan-datastore:list
   */
  public function list() {
    $list = $this->datastoreService->list();
    $rows = [];
    foreach ($list as $uuid => $item) {
      $row = [
        'uuid' => $uuid,
        'fileName' => $item->fileName,
        'fileFetcherStatus' => $item->fileFetcherStatus,
        'fileFetcherBytes' => \format_size($item->fileFetcherBytes) . " ($item->fileFetcherPercentDone%)",
        'importerStatus' => $item->importerStatus,
        'importerBytes' => \format_size($item->importerBytes) . " ($item->importerPercentDone%)",
      ];
      $rows[] = $row;
    }
    return new RowsOfFields($rows);
  }

  /**
   * Drop.
   *
   * @param string $uuid
   *   The uuid of a dataset.
   *
   * @command dkan-datastore:drop
   */
  public function drop($uuid) {
    try {
      // Load metadata with both identifier and data for this request.
      drupal_static('dkan_data_dereference_method', ValueReferencer::DEREFERENCE_OUTPUT_VERBOSE);
      $this->datastoreService->drop($uuid);
    }
    catch (\Exception $e) {
      $this->logger->error("We were not able to load the entity with uuid {$uuid}");
      $this->logger->debug($e->getMessage());
    }
  }

}
