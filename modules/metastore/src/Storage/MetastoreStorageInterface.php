<?php

namespace Drupal\metastore\Storage;

use Contracts\BulkRetrieverInterface;
use Contracts\StorerInterface;

/**
 * Interface for all metastore storage classes.
 */
interface MetastoreStorageInterface extends StorerInterface, BulkRetrieverInterface {

  /**
   * Retrieve.
   *
   * @param string $id
   *   The identifier for the data.
   *
   * @return string|HydratableInterface
   *   The data or null if no data could be retrieved.
   */
  public function retrieve(string $id);

  /**
   * Retrieve all.
   *
   * @return array
   *   An array of metadata objects.
   */
  public function retrieveAll(): array;

  /**
   * Retrieve a limited range of metadata items.
   *
   * @param int $start
   *   Offset.
   * @param int $length
   *   Number to retrieve.
   *
   * @return array
   *   An array of metadata objects.
   */
  public function retrieveRange(int $start, int $length): array;

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
   * Retrieve the json metadata from an entity only if it is published.
   *
   * @param string $uuid
   *   The identifier.
   *
   * @return string|null
   *   The entity's json metadata, or NULL if the entity was not found.
   */
  public function retrievePublished(string $uuid) : ?string;

  /**
   * Publish the latest version of a data entity.
   *
   * @param string $uuid
   *   Identifier.
   *
   * @return string
   *   Identifier.
   */
  public function publish(string $uuid) : string;

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
   * @param string|HydratableInterface $data
   *   The data to be stored.
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
