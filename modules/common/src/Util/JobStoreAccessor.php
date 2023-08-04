<?php

namespace Drupal\common\Util;

use Drupal\common\Storage\JobStore;

/**
 * Utility class to access protected JobStore elements.
 *
 * ONLY to be used for mitigating/updating legacy job store database tables.
 */
class JobStoreAccessor extends JobStore {

  /**
   * Get the non-deprecated table name.
   *
   * @return string
   *   The non-deprecated table name.
   */
  public function accessTableName(): string {
    return $this->getHashedTableName();
  }

  /**
   * Get the deprecated table name.
   *
   * @return string
   *   The deprecated table name.
   */
  public function accessDeprecatedTableName(): string {
    return $this->getDeprecatedTableName();
  }

  /**
   * Set the table name to use for this job store.
   *
   * @param string $identifier
   *   New identifier.
   */
  public function setTableName(string $identifier): void {
    $this->tableName = $identifier;
  }

}
