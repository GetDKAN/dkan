<?php

namespace Drupal\metastore\Factory;

/**
 * Interface MetastoreItemFactoryInterface.
 *
 * Used for service dkan.metastore.metastore_item_factory. Override the service
 * to use different logic for producing a MetastoreItemInterface object from
 * just an indentifier.
 */
interface MetastoreEntityItemFactoryInterface extends MetastoreItemFactoryInterface {

  /**
   * Get the entity type used for this item factory.
   *
   * @return string
   *   The entity type ID, e.g. 'node'.
   */
  public static function getEntityType();

  /**
   * Get the bundles, if any, used by this factory for storing item entities.
   *
   * @return array
   *   Array of bundle IDs.
   */
  public static function getBundles();

  /**
   * Get the name of the entity field or property used to store metadata.
   *
   * @return string
   *   Field API name.
   */
  public static function getMetadataField();

}
