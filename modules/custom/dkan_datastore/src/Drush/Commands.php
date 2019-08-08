<?php

namespace Drupal\dkan_datastore\Drush;

use Drupal\dkan_data\ValueReferencer;


use Drush\Commands\DrushCommands;

/**
 * Drush commands for controlling the datastore.
 *
 * @codeCoverageIgnore
 */
class Commands extends DrushCommands {

  protected $datastoreService;
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
   *   The uuid of a dataset.
   * @param bool $deferred
   *   Whether or not the process should be deferred to a queue.
   *
   * @TODO pass configurable options for csv delimiter, quite, and escape characters.
   * @command dkan-datastore:import
   */
  public function import($uuid, $deferred = FALSE) {

    try {
      // Load metadata with both identifier and data for this request.
      drupal_static('dkan_data_dereference_method', ValueReferencer::DEREFERENCE_OUTPUT_BOTH);

      $this->datastoreService->import($uuid, $deferred);
    }
    catch (\Exception $e) {
      $this->logger->error("We were not able to load the entity with uuid {$uuid}");
      $this->logger->debug($e->getMessage());
    }
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
      drupal_static('dkan_data_dereference_method', ValueReferencer::DEREFERENCE_OUTPUT_BOTH);
      $this->datastoreService->drop($uuid);
    }
    catch (\Exception $e) {
      $this->logger->error("We were not able to load the entity with uuid {$uuid}");
      $this->logger->debug($e->getMessage());
    }
  }

}
