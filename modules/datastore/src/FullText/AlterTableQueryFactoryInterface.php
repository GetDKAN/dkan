<?php

namespace Drupal\datastore\FullText;

/**
 * Alter table query factory interface.
 */
interface AlterTableQueryFactoryInterface {

  /**
   * Build alter table query instance.
   *
   * @param string $datastore_table
   *   Datastore table being altered.
   * @param array $indexes
   *   Array of fulltext indexes to apply.
   *
   * @return \Drupal\datastore\FullText\AlterTableQueryInterface
   *   An alter table query instance.
   */
  public function getQuery(string $datastore_table, array $indexes): AlterTableQueryInterface;

  /**
   * Set the wait_timeout for the default database connection.
   *
   * @param int $timeout
   *   Wait timeout in seconds.
   *
   * @return self
   *   Return instance of `$this` for chaining.
   */
  public function setConnectionTimeout(int $timeout): self;

}
