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
interface DatabaseTableInterface extends StorerInterface, RetrieverInterface, RemoverInterface, BulkStorerInterface, CountableInterface, BulkRetrieverInterface {

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

  /**
   * Set the schema for the current table.
   *
   * @param array $schema
   *   A Drupal schema array.
   */
  public function setSchema(array $schema): void;

  /**
   * Create the table in the db if it does not yet exist.
   *
   * @throws \Exception
   *   Can throw any DB-related exception. Notably, can throw
   *   \Drupal\Core\Database\SchemaObjectExistsException if the table already
   *   exists when we try to create it.
   */
  public function setTable(): void;

  /**
   * Get the schema array for this table.
   *
   * @return array
   *   Drupal Schema API array.
   */
  public function getSchema(): array;

}
