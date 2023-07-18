<?php

namespace Drupal\common\Util;

use Drupal\common\Storage\JobStore;

/**
 * Utility class to access protected JobStore elements.
 *
 * ONLY to be used for mitigating/updating legacy job store database tables.
 */
class JobStoreAccessor extends JobStore {

  public function accessTableName() {
    return $this->getTableName();
  }

  public function accessDeprecatedTableName() {
    return $this->getDeprecatedTableName();
  }

  public function setTableName(string $identifier) {
    return $this->tableName;
  }

}
