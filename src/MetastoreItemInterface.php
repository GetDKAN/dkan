<?php

namespace Drupal\dkan;

use Drupal\Core\Cache\CacheableDependencyInterface;

/**
 * Data.
 */
interface MetastoreItemInterface extends CacheableDependencyInterface {

  /**
   * Getter.
   */
  public function getModifiedDate();

  /**
   * Getter.
   */
  public function getIdentifier();

  /**
   * The unaltered version of the metadata.
   */
  public function getRawMetadata();

  /**
   * Protected.
   */
  public function getSchemaId();

  /**
   * Get the metadata itself from the item.
   *
   * @param bool $dereferenced
   *   Whether to dereference the JSON values, defaults to true.
   *
   * @return object
   *   Decoded JSON object.
   */
  public function getMetadata(bool $dereferenced = TRUE): object;

  /**
   * Replace the metadata for an item.
   *
   * @param object $metadata
   * @return void
   */
  public function setMetadata(object $metadata): void;

  /**
   * Setter.
   */
  public function setIdentifier(string $identifier);

  /**
   * Set the title of the item.
   *
   * @param string $title
   *   A title string.
   */
  public function setTitle(string $title): void;

  /**
   * Checks if the item has just been created.
   *
   * @return bool
   *   True if a new item.
   */
  public function isNew(): bool;

}
