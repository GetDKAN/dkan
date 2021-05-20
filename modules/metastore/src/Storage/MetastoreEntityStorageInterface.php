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

  /**
   * Load a entity's latest revision, given a dataset's uuid.
   *
   * @param string $uuid
   *   The dataset identifier.
   *
   * @return \Drupal\Core\Entity\EditorialContentEntityBase|null
   *   The entity's latest revision, if found.
   */
  public function getEntityLatestRevision(string $uuid);

  /**
   * Load a Data entity's published revision.
   *
   * @param string $uuid
   *   The dataset identifier.
   *
   * @return \Drupal\Core\Entity\EditorialContentEntityBase|null
   *   The entity's published revision, if found.
   */
  public function getEntityPublishedRevision(string $uuid);

  /**
   * Get the entity id from the dataset identifier.
   *
   * @param string $uuid
   *   The dataset identifier.
   *
   * @return int|null
   *   The entity id, if found.
   */
  public function getEntityIdFromUuid(string $uuid) : ?int;

  /**
   * Return the default moderation state of our custom dkan_publishing workflow.
   *
   * @return string
   *   The default moderation state for this entity.
   */
  public function getDefaultModerationState();

}
