<?php

namespace Drupal\harvest;

use Drupal\Core\Database\Connection;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\harvest\Storage\DatabaseTableFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * DKAN Harvest utility service for maintenance tasks.
 *
 * These methods generally exist to support a thin Drush layer. These are
 * methods that we don't need in the HarvestService object.
 */
class HarvestUtility implements ContainerInjectionInterface {

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
   * Create.
   *
   * @inheritdoc
   */
  public static function create(ContainerInterface $container) {
    return new self(
      $container->get('dkan.harvest.service'),
      $container->get('dkan.harvest.storage.database_table'),
      $container->get('database'),
    );
  }

  /**
   * Constructor.
   */
  public function __construct(
    HarvestService $harvestService,
    DatabaseTableFactory $storeFactory,
    Connection $connection
  ) {
    $this->harvestService = $harvestService;
    $this->storeFactory = $storeFactory;
    $this->connection = $connection;
  }

  /**
   * Get the plan ID from a given harvest table name.
   *
   * Harvest table names are assumed to look like this:
   * harvest_ID_that_might_have_underscores_[something]. For example:
   * 'harvest_id_here_run'.
   *
   * @param $table_name
   *   The table name.
   *
   * @return string
   *   The ID gleaned from the table name. If no ID could be gleaned, returns
   *   an empty string.
   */
  public static function planIdFromTableName($table_name): string {
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

    $tables = [];
    foreach ([
      // @todo Figure out an expression for harvest_%_thing, since underscore
      //   is a special character.
      'harvest%runs',
      'harvest%items',
      'harvest%hashes',
    ] as $table_expression) {
      if ($found_tables = $this->connection->schema()->findTables($table_expression)) {
        $tables = array_merge($tables, $found_tables);
      }
    }

    $orphan_ids = [];
    // Find IDs that are not in the existing plans.
    foreach ($tables as $table_name) {
      $plan_id = static::planIdFromTableName($table_name);
      if (!in_array($plan_id, $existing_plans)) {
        $orphan_ids[$plan_id] = $plan_id;
      }
    }
    return $orphan_ids;
  }

  /**
   * Remove existing harvest data tables for the given plan identifier.
   *
   * Will not remove data tables for existing plans.
   *
   * @param $plan_id
   *   Plan identifier to work with.
   *
   * @return void
   */
  public function destructOrphanTables($plan_id): void {
    if (!in_array($plan_id, $this->harvestService->getAllHarvestIds())) {
      foreach ([
        'harvest_' . $plan_id . '_runs',
        'harvest_' . $plan_id . '_items',
        'harvest_' . $plan_id . '_hashes',
      ] as $table) {
        $this->storeFactory->getInstance($table)->destruct();
      }
    }
  }

}
