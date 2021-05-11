<?php

namespace Drupal\metastore\Storage;

/**
 * Storage interface specifically for using drupal entities.
 */
interface MetastoreEntityStorageInterface extends MetastoreStorageInterface {

  /**
   * Get the entity type used in this storage class.
   *
   * @return string
   *   Entity type.
   */
  public static function getEntityType();

  /**
   * Get the bundles used in this storage class.
   *
   * @return array
   *   Array of bundle names.
   */
  public static function getBundles();

  /**
   * Get the name of the property or field used to store the JSON metadata.
   *
   * @return string
   *   Field name.
   */
  public static function getMetadataField();

}
