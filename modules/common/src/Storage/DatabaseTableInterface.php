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
  public function destruct();

  /**
   * Query.
   */
  public function query(Query $query);

  /**
   * Return the primary key for the table.
   *
   * @return string
   *   Primary key name.
   */
  public function primaryKey();

}
