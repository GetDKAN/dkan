<?php

namespace Drupal\metastore;

/**
 * Data.
 */
interface MetastoreItemInterface {
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

}
