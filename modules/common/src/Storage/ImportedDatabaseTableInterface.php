<?php

namespace Drupal\common\Storage;

/**
 * Databaset table interface.
 */
interface ImportedDatabaseTableInterface extends DatabaseTableInterface {

  /**
   *
   *
   * @return bool
   *   TRUE if the table exists and data has been imported into it. FALSE
   *   otherwise.
   */
  public function hasBeenImported(): bool;

}
