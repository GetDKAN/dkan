<?php

namespace Drupal\harvest;

use Drupal\Core\Database\Connection;
use Drupal\harvest\Storage\DatabaseTableFactory;
use Drupal\harvest\Storage\HarvestHashesDatabaseTableFactory;
use Psr\Log\LoggerInterface;

/**
 * DKAN Harvest utility service for maintenance tasks.
 *
 * These methods generally exist to support a thin Drush layer. These are
 * methods that we don't need in the HarvestService object.
 */
class HarvestUtility {

  /**
   * Harvest service.
   *
   * @var \Drupal\harvest\HarvestService
   */
  private HarvestService $harvestService;

  /**
   * Service to instantiate storage objects for Harvest plan storage.
   *
   * @var \Drupal\harvest\Storage\DatabaseTableFactory
   */
  private DatabaseTableFactory $storeFactory;

  /**
   * Database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  private Connection $connection;

  /**
   * The harvest hashes database table factory service.
   *
   * @var \Drupal\harvest\Storage\HarvestHashesDatabaseTableFactory
   */
  private HarvestHashesDatabaseTableFactory $hashesFactory;

  /**
   * Logger channel service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  private LoggerInterface $logger;

  /**
   * Constructor.
   */
  public function __construct(
    HarvestService $harvestService,
    DatabaseTableFactory $storeFactory,
    HarvestHashesDatabaseTableFactory $hashesFactory,
    Connection $connection,
    LoggerInterface $loggerChannel
  ) {
    $this->harvestService = $harvestService;
    $this->storeFactory = $storeFactory;
    $this->hashesFactory = $hashesFactory;
    $this->connection = $connection;
    $this->logger = $loggerChannel;
  }

  /**
   * Get the plan ID from a given harvest table name.
   *
   * Harvest table names are assumed to look like this:
   * harvest_planID_that_might_have_underscores_[type], where [type] is one of
   * hashes, items, or runs. For example: 'harvest_ABC_123_runs'.
   *
   * @param string $table_name
   *   The table name.
   *
   * @return string
   *   The ID gleaned from the table name. If no ID could be gleaned, returns
   *   an empty string.
   */
  public static function planIdFromTableName(string $table_name): string {
    $name_explode = explode('_', $table_name);
    if (count($name_explode) < 3) {
      return '';
    }
    // Remove first and last item.
    array_shift($name_explode);
    array_pop($name_explode);
    return implode('_', $name_explode);
  }

  /**
   * Find harvest IDs with data tables that aren't in the harvest_plans table.
   *
   * @return array
   *   Array of orphan plan ids, as both key and value. Empty if there are no
   *   orphaned plan ids.
   */
  public function findOrphanedHarvestDataIds(): array {
    $existing_plans = $this->harvestService->getAllHarvestIds();

    $table_names = $this->findAllHarvestDataTables();

    $orphan_ids = [];
    // Find IDs that are not in the existing plans.
    foreach ($table_names as $table_name) {
      $plan_id = static::planIdFromTableName($table_name);
      if (!in_array($plan_id, $existing_plans)) {
        $orphan_ids[$plan_id] = $plan_id;
      }
    }
    return $orphan_ids;
  }

  /**
   * Find all the potential harvest data tables names in the database.
   *
   * @return array
   *   All the table names that might be harvest data tables.
   */
  protected function findAllHarvestDataTables(): array {
    $tables = [];
    foreach ([
      'harvest_%_runs',
      'harvest_%_items',
      'harvest_%_hashes',
    ] as $table_expression) {
      if ($found_tables = $this->connection->schema()->findTables($table_expression)) {
        $tables = array_merge($tables, $found_tables);
      }
    }
    return $tables;
  }

  /**
   * Remove existing harvest data tables for the given plan identifier.
   *
   * Will not remove data tables for existing plans.
   *
   * @param string $plan_id
   *   Plan identifier to work with.
   */
  public function destructOrphanTables(string $plan_id): void {
    if (!in_array($plan_id, $this->harvestService->getAllHarvestIds())) {
      foreach ([
        'harvest_' . $plan_id . '_runs',
        'harvest_' . $plan_id . '_items',
      ] as $table) {
        $this->storeFactory->getInstance($table)->destruct();
      }
    }
  }

  /**
   * Convert a table to use the harvest_hash entity.
   *
   * @param string $plan_id
   *   Harvest plan ID to convert.
   */
  public function convertHashTable(string $plan_id) {
    $old_hash_table = $this->storeFactory->getInstance('harvest_' . $plan_id . '_hashes');
    $hash_table = $this->hashesFactory->getInstance($plan_id);
    foreach ($old_hash_table->retrieveAll() as $id) {
      if ($data = $old_hash_table->retrieve($id)) {
        $hash_table->store($data, $id);
      }
    }
  }

  /**
   * Update all the harvest hash tables to use entities.
   *
   * This will move all harvest hash information to the updated schema,
   * including data which does not have a corresponding hash plan ID.
   *
   * Outdated tables will be removed.
   */
  public function harvestHashUpdate() {
    $plan_ids = array_merge(
      $this->harvestService->getAllHarvestIds(),
      array_values($this->findOrphanedHarvestDataIds())
    );
    foreach ($plan_ids as $plan_id) {
      $this->logger->notice('Converting hashes for ' . $plan_id);
      $this->convertHashTable($plan_id);
      $this->storeFactory->getInstance('harvest_' . $plan_id . '_hashes')
        ->destruct();
    }
  }

}
