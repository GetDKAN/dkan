<?php

namespace Drupal\harvest;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\harvest\Entity\HarvestRunRepository;
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
   * Harvest run entity repository service.
   *
   * @var \Drupal\harvest\Entity\HarvestRunRepository
   */
  private HarvestRunRepository $runRepository;

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
      $container->get('dkan.harvest.storage.harvest_run_repository')
    );
  }

  /**
   * Constructor.
   */
  public function __construct(
    HarvestService $harvestService,
    DatabaseTableFactory $storeFactory,
    Connection $connection,
    HarvestRunRepository $runRepository
  ) {
    $this->harvestService = $harvestService;
    $this->storeFactory = $storeFactory;
    $this->connection = $connection;
    $this->runRepository = $runRepository;
  }

  /**
   * Get the plan ID from a given harvest table name.
   *
   * Harvest table names are assumed to look like this:
   * harvest_ID_that_might_have_underscores_[something]. For example:
   * 'harvest_ABC_123_runs'.
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
    $orphan_ids = [];

    // Plan IDs from the plans table.
    $existing_plans = $this->harvestService->getAllHarvestIds();

    // Potential orphan plan IDs in the runs table.
    $run_ids = $this->runRepository->getUniqueHarvestPlanIds();
    foreach (array_diff($run_ids, $existing_plans) as $run_id) {
      $orphan_ids[$run_id] = $run_id;
    }

    // Use harvest data table names to glean more potential orphan harvest plan
    // ids.
    foreach ($this->findAllHarvestDataTables() as $table_name) {
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
        'harvest_' . $plan_id . '_hashes',
      ] as $table) {
        $this->storeFactory->getInstance($table)->destruct();
      }
    }
  }

}
