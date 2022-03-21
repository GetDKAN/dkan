<?php

namespace Drupal\datastore\DataDictionary;

/**
 * Alter table query factory interface.
 */
interface AlterTableQueryFactoryInterface {

  /**
   * Build alter table query instance.
   *
   * @param string $datastore_table
   *   Datastore table being altered.
   * @param array $dictionary_fields
   *   Data-dictionary fields list.
   *
   * @return \Drupal\datastore\DataDictionary\AlterTableQueryInterface
   *   An alter table query instance.
   */
  public function getQuery(string $datastore_table, array $dictionary_fields): AlterTableQueryInterface;

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
