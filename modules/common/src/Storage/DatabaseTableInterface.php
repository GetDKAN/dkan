<?php

namespace Drupal\common\Storage;

use Contracts\BulkRetrieverInterface;
use Contracts\BulkStorerInterface;
use Contracts\CountableInterface;
use Contracts\RemoverInterface;
use Contracts\RetrieverInterface;
use Contracts\StorerInterface;

/**
 * Databaset table interface.
 */
interface DatabaseTableInterface extends StorerInterface, RetrieverInterface, RemoverInterface, BulkStorerInterface, CountableInterface, BulkRetrieverInterface {

  /**
   * Remove the table from the database.
   */
  public function destruct();

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
