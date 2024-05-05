<?php

namespace Drupal\common\Storage;

use Drupal\common\Contracts\BulkRetrieverInterface;
use Drupal\common\Contracts\BulkStorerInterface;
use Drupal\common\Contracts\RemoverInterface;
use Drupal\common\Contracts\RetrieverInterface;
use Drupal\common\Contracts\StorerInterface;

/**
 * Databaset table interface.
 */
interface DatabaseTableInterface extends
  BulkRetrieverInterface,
  BulkStorerInterface,
  \Countable,
  RemoverInterface,
  RetrieverInterface,
  StorerInterface {

  /**
   * Remove the table from the database.
   */
  public function destruct();

  /**
   * Perform a SELECT query against the table.
   */
  public function query(Query $query);

  /**
   * Return the primary key for the table.
   *
   * @return string
   *   Primary key name.
   */
  public function primaryKey();

  /**
   * Set the schema for the current table.
   *
   * @param array $schema
   *   A Drupal schema array.
   */
  public function setSchema(array $schema): void;

  /**
   * Get the schema array for this table.
   *
   * @return array
   *   Drupal Schema API array.
   */
  public function getSchema(): array;

}
