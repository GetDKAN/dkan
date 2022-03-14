<?php

namespace Drupal\datastore\DataDictionary;

abstract class AlterTableQueryFactoryBase implements AlterTableQueryFactoryInterface {

  /**
   * Get the class to use in the factory.
   *
   * @return string
   *   Class implementing the AlterTableQueryInterface.
   */
  abstract protected function getQueryClass(): string;

  /**
   * Set the wait_timeout for the default database connection.
   *
   * @param int $timeout
   *   Wait timeout in seconds.
   */
  abstract public function setConnectionTimeout(int $timeout): void;

  /**
   *
   */
  public function __construct($connection, $alter_table_query, $date_format_converter) {
    $this->connection = $connection;
    $this->alterTableQuery = $alter_table_query;
    $this->dateFormatConverter = $date_format_converter;
  }

  /**
   * Build alter table query class instance.
   *
   * @param string $datastore_table
   *   Datastore table being altered.
   * @param string $dictionary_fields
   *   Data-dictionary fields list.
   * @param int|null $timeout
   *   Optional SQL connection timeout.
   *
   */
  public function get(string $datastore_table, array $dictionary_fields, int $timeout = NULL): AlterTableQueryInterface {
    $query_class = $this->getQueryClass();
    return new $query_class(
      $this->connection,
      $this->alterTableQuery,
      $this->dateFormatConverter,
      $datastore_table,
      $dictionary_fields,
      $timeout
    );
  }

}
