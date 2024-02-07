<?php

namespace Drupal\metastore\Storage;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\RevisionableStorageInterface;

/**
 * Storage interface specifically for using drupal entities.
 */
interface MetastoreEntityStorageInterface extends MetastoreStorageInterface {

  /**
   * Get entity storage.
   *
   * @return \Drupal\Core\Entity\RevisionableStorageInterface
   *   Entity storage.
   */
  public function getEntityStorage(): RevisionableStorageInterface;

  /**
   * Load a Data entity's published revision.
   *
   * @param string $uuid
   *   The dataset identifier.
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface|null
   *   The entity's published revision, if one is found.
   */
  public function getEntityPublishedRevision(string $uuid): ?ContentEntityInterface;

  /**
   * Load a entity's latest revision, given a dataset's uuid.
   *
   * @param string $uuid
   *   The dataset identifier.
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface|null
   *   The entity's latest revision, if found.
   */
  public function getEntityLatestRevision(string $uuid): ?ContentEntityInterface;

}
