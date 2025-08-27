<?php

namespace Drupal\metastore;

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
   * Get the node schema identifier.
   *
   * @return string
   *   The Data node schema identifier, such as 'dataset' or 'distribution'.
   */
  public function getSchemaId();

  /**
   * Protected.
   */
  public function getMetadata();

  /**
   * Protected.
   */
  public function setMetadata($metadata);

  /**
   * Setter.
   */
  public function setIdentifier($identifier);

  /**
   * Setter.
   */
  public function setTitle($title);

  /**
   * Is New.
   */
  public function isNew();

  /**
   * Get the relevant entity.
   *
   * If the implementation is an entity itself, this should simply
   * return $this.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The wrapped entity.
   */
  public function getEntity();

}
