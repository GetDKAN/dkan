<?php

namespace Drupal\harvest\Storage;

use Drupal\common\Storage\DrupalEntityDatabaseTableBase;
use Drupal\Core\Database\Connection;
use Drupal\common\Storage\AbstractDatabaseTable;

class HarvestHashesEntityDatabaseTable extends DrupalEntityDatabaseTableBase {

  protected static $entityType = 'harvest_hash';

}
