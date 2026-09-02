<?php

namespace Drupal\dkan_metastore;

use Drupal\Core\Cache\CacheableDependencyInterface;

/**
 * Metastore item interface.
 *
 * Use an instance of \Drupal\metastore\Factory\MetastoreItemFactoryInterface
 * to create these.
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
   * Get the metadata that was present when it was originally generated.
   *
   * This is the metadata that was present on the entity when it was originally
   * wrapped.
   *
   * @return mixed
   *   The JSON-decoded metadata or NULL.
   *
   * @see self::getMetadata()
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
   * Get the item's metadata.
   *
   * @return mixed
   *   The JSON-decoded metadata or NULL.
   *
   * @see self::getRawMetadata()
   */
  public function getMetadata();

  /**
   * Set the metadata for this item.
   *
   * @param mixed $metadata
   *   The new metadata. Should be JSON encode-able.
   */
  public function setMetadata($metadata);

  /**
   * Set the identifier for this item.
   *
   * @param mixed $identifier
   *   Metastore item identifier.
   */
  public function setIdentifier($identifier);

  /**
   * Set the title of this item.
   *
   * @param mixed $title
   *   Metastore item title.
   */
  public function setTitle($title);

  /**
   * Is New.
   */
  public function isNew();

  /**
   * Get an entity which represents the metadata item.
   *
   * If the implementation is that entity, it should return itself.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The wrapped entity.
   */
  public function getEntity();

}
