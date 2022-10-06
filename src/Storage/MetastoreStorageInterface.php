<?php

namespace Drupal\dkan\Storage;

use Drupal\dkan\MetastoreItemInterface;

/**
 * Interface for all metastore storage classes.
 */
interface MetastoreStorageInterface {

  /**
   * Count objects of the current schema ID.
   *
   * @param bool $unpublished
   *   Whether to include unpublished items.
   *
   * @return int
   *   Count.
   */
  public function count(bool $unpublished = FALSE): int;

  /**
   * Retrieve a metadata string by ID.
   *
   * @param string $id
   *   The identifier for the data.
   *
   * @return \Drupal\dkan\MetastoreItemInterface
   *   A MetastoreItemObject.
   *
   * @throws \Drupal\dkan\Exception\MissingObjectException
   *   When attempting to retrieve metadata fails.
   */
  public function retrieve(string $id): MetastoreItemInterface;

  /**
   * Retrieve multiple metadata items.
   *
   * @param int|null $start
   *   Offset. NULL for no range, zero for beginning of set.
   * @param int|null $length
   *   Number of items to retrieve. NULL for no limit.
   * @param bool $unpublished
   *   Whether to include unpublished items in the results.
   *
   * @return \Drupal\dkan\MetastoreItemInterface[]
   *   An array of Metastore item objects.
   */
  public function retrieveMultiple(?int $start = NULL, ?int $length = NULL, bool $unpublished = FALSE): array;

  /**
   * Retrieve just identifiers.
   *
   * @param int|null $start
   *   Offset.
   * @param int|null $length
   *   Number of identifiers to retrieve. NULL for no limit.
   * @param bool $unpublished
   *   Whether to include unpublished items in the results.
   *
   * @return string[]
   *   An array of metastore item identifiers.
   */
  public function retrieveIds(?int $start, ?int $length, bool $unpublished = FALSE): array;

  /**
   * Retrieve all metadata items that contain a particular exact string.
   *
   * This will be used to query raw, referenced metadata in the storage system.
   * Use the metastore search service for more precise/cofigurable searching
   * and searching dereferenced, user-facing metadata.
   *
   * @param string $string
   *   The string to match within raw metastore item JSON.
   * @param bool $caseSensitive
   *   Whether to search metadata in a case-sensitive manner.
   *
   * @return array
   *   An array of metadata objects.
   */
  public function retrieveContains(string $string, bool $caseSensitive): array;

  /**
   * Determine whether the given metastore item is published.
   *
   * @param string $uuid
   *   The ID of the metastore item in question.
   *
   * @return bool
   *   Whether the given metastore item is published.
   */
  public function isPublished(string $uuid) : bool;

  /**
   * Remove.
   *
   * @param string $id
   *   The identifier for the data.
   */
  public function remove(string $id);

  /**
   * Store.
   *
   * @param object|array $data
   *   The data to be stored, as a structured object or assoc array.
   * @param string $id
   *   The identifier for the data. If the act of storing generates the
   *   id, there is no need to pass one.
   *
   * @return string
   *   The identifier.
   *
   * @throws \Exception
   *   Issues storing the data.
   */
  public function store($data, string $id = NULL): string;

  /**
   * Retrieve by hash.
   *
   * @param string $hash
   *   The hash for the data.
   * @param string $schemaId
   *   The schema ID.
   *
   * @return string|null
   *   The uuid of the item with that hash.
   */
  public function retrieveByHash($hash, $schemaId);

}
