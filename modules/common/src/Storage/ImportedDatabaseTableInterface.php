<?php

namespace Drupal\common\Storage;

use Contracts\RemoverInterface;
use Contracts\RetrieverInterface;
use Contracts\StorerInterface;
use Contracts\BulkRetrieverInterface;
use Contracts\BulkStorerInterface;
use Contracts\CountableInterface;

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
