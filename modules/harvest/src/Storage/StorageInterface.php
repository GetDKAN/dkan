<?php

namespace Drupal\harvest\Storage;

/**
 * Interface for harvest storage.
 */
interface StorageInterface {

  /**
   * Store data with an identifier.
   *
   * @param mixed $data
   *   The data to be stored.
   * @param string|null $id
   *   The identifier for the data. If the act of storing generates the
   *   id, there is no need to pass one.
   *
   * @return string
   *   The identifier.
   *
   * @throws \Exception
   *   Issues storing the data.
   */
  public function store($data, ?string $id = NULL): string;

  /**
   * Retrieve all.
   *
   * @return array
   *   An array of ids.
   */
  public function retrieveAll(): array;

}
