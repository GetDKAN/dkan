<?php

namespace Drupal\common\Storage;

use Contracts\RemoverInterface;
use Contracts\RetrieverInterface;
use Contracts\StorerInterface;
use Dkan\Datastore\Storage\StorageInterface;

/**
 * Databaset table interface.
 */
interface DatabaseTableInterface extends StorageInterface, StorerInterface, RetrieverInterface, RemoverInterface {

  /**
   * Destroy.
   */
  public function destroy();

  /**
   * Query.
   */
  public function query(Query $query);

}
