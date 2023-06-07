<?php

namespace Drupal\common\Storage;

/**
 * Determine if the table has already been imported.
 *
 * In some cases, a very large table import might result in a correct table,
 * even if the DB connection was lost from the PHP session.
 *
 * Implement this interface to determine whether the table has already been
 * imported, and therefore does not need to be imported again.
 */
interface ImportedDatabaseTableInterface extends DatabaseTableInterface {

  /**
   * Determine if the table has already been imported.
   *
   * @return bool
   *   TRUE if the table exists and data has been imported into it. FALSE
   *   otherwise.
   */
  public function hasBeenImported(): bool;

}
