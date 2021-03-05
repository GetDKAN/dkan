<?php

namespace Drupal\metastore\Storage;

use RootedData\RootedJsonData;

interface MetastoreStorageInterface
{

  /**
   * Retrieve.
   *
   * @param string $id
   *   The identifier for the data.
   *
   * @return string | HydratableInterface
   *   The data or null if no data could be retrieved.
   */
  public function retrieve(string $id);

  /**
   * Retrieve all.
   *
   * @return array
   *   An array of ids.
   */
  public function retrieveAll(): array;

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
   * @param \RootedData\RootedJsonData $data
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
  public function store(RootedJsonData $data, string $id = null): string;

}
