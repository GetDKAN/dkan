<?php

namespace Drupal\harvest\Entity;

use Drupal\common\Storage\DrupalEntityDatabaseTableBase;

/**
 * Use a Drupal entity for harvest_plans db table.
 */
class HarvestPlanEntityDatabaseTable extends DrupalEntityDatabaseTableBase {

  /**
   * {@inheritdoc}
   */
  protected static $entityType = 'harvest_plan';

}
