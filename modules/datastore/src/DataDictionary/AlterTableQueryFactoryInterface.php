<?php

namespace Drupal\datastore\DataDictionary;

interface AlterTableQueryFactoryInterface {

  public function get(string $datastore_table, array $dictionary_fields, int $timeout): AlterTableQueryInterface;

  /**
   * Set the wait_timeout for the default database connection.
   *
   * @param int $timeout
   *   Wait timeout in seconds.
   */
  public function setConnectionTimeout(int $timeout): void;

}
