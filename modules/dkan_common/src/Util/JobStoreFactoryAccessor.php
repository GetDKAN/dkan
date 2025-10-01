<?php

namespace Drupal\common\Util;

use Drupal\common\Storage\JobStoreFactory;

/**
 * Utility class to access protected JobStoreFactory elements.
 *
 * ONLY to be used for mitigating/updating legacy job store database tables.
 */
class JobStoreFactoryAccessor extends JobStoreFactory {

  /**
   * Get the non-deprecated table name.
   *
   * @return string
   *   The non-deprecated table name.
   */
  public function accessTableName(string $identifier): string {
    return $this->getHashedTableName($identifier);
  }

  /**
   * Get the deprecated table name.
   *
   * @return string
   *   The deprecated table name.
   */
  public function accessDeprecatedTableName(string $identifier): string {
    return $this->getDeprecatedTableName($identifier);
  }

}
