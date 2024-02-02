<?php

namespace Drupal\harvest\Storage;

use Drupal\common\Storage\DrupalEntityDatabaseTableBase;

/**
 * Use a Drupal entity for harvest_plans db table.
 *
 * Currently handles the harvest_plans table.
 *
 * @see \Drupal\harvest\Storage\DatabaseTableFactory::getDatabaseTable()
 */
class HarvestPlanEntityDatabaseTable extends DrupalEntityDatabaseTableBase {

  /**
   * {@inheritdoc}
   */
  protected string $entityType = 'harvest_plan';

}
