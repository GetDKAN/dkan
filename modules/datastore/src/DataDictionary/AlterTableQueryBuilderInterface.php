<?php

namespace Drupal\datastore\DataDictionary;

use RootedData\RootedJsonData;

/**
 * Alter table query builder interface.
 */
interface AlterTableQueryBuilderInterface {

  /**
   * Build alter table query instance.
   *
   * @return \Drupal\datastore\DataDictionary\AlterTableQueryInterface
   *   An alter table query instance.
   */
  public function getQuery(): AlterTableQueryInterface;

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

  /**
   * Set the table for the alter query to be built for.
   *
   * @param string $table
   *   Table name.
   *
   * @return self
   *   Return instance of `$this` for chaining.
   */
  public function setTable(string $table): self;

  /**
   * Extract and add fields and indexes from the given data-dictionary.
   *
   * @param \RootedData\RootedJsonData $dictionary
   *   Data-dictionary.
   *
   * @return self
   *   Return instance of `$this` for chaining.
   */
  public function addDataDictionary(RootedJsonData $dictionary): self;

  /**
   * Add SQL table fields to alter query.
   *
   * @param array $fields
   *   Table fields in the format of:
   *   @code
   *   [
   *     [
   *       'name' => 'some_date_field',
   *       'type' => 'date',
   *       'format' => '%Y-%m-%d' // optional
   *     ],
   *   ]
   *   @endcode
   *
   * @return self
   *   Return instance of `$this` for chaining.
   */
  public function addFields(array $fields): self;

  /**
   * Add SQL table indexes to alter query.
   *
   * @param array $indexes
   *   Table indexes in the format of:
   *   @code
   *   [
   *     'name' => 'index1',
   *     'type' => 'btree', // optional
   *     'fields' => [
   *       [
   *         'name' => 'field_name',
   *         'length' => 25, // optional
   *       ],
   *     ],
   *   ]
   *   @endcode
   *
   * @return self
   *   Return instance of `$this` for chaining.
   */
  public function addIndexes(array $indexes): self;

}
