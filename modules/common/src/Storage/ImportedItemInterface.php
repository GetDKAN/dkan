<?php

namespace Drupal\common\Storage;

/**
 * Determine if an item has already been imported.
 *
 * In some cases, a very large table import might result in a correct table,
 * even if the DB connection was lost from the PHP session.
 *
 * Similarly, very large files might have been transferred correctly, even if
 * the PHP session timed out or was otherwise unable to mark them as imported.
 *
 * Implement this interface to determine whether the item has already been
 * imported, and therefore does not need to be imported again.
 */
interface ImportedItemInterface extends DatabaseTableInterface {

  /**
   * Determine if the item has already been imported.
   *
   * @return bool
   *   TRUE if the item does not need to be re-imported. FALSE otherwise.
   */
  public function hasBeenImported(): bool;

}
