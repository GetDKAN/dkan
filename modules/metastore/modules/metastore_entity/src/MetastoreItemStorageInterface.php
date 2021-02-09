<?php

namespace Drupal\metastore_entity;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\metastore_entity\Entity\MetastoreItemInterface;

/**
 * Defines the storage handler class for Metastore item entities.
 *
 * This extends the base storage class, adding required special handling for
 * Metastore item entities.
 *
 * @ingroup metastore_entity
 */
interface MetastoreItemStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Metastore item revision IDs for a specific Metastore item.
   *
   * @param \Drupal\metastore_entity\Entity\MetastoreItemInterface $entity
   *   The Metastore item entity.
   *
   * @return int[]
   *   Metastore item revision IDs (in ascending order).
   */
  public function revisionIds(MetastoreItemInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Metastore item author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Metastore item revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\metastore_entity\Entity\MetastoreItemInterface $entity
   *   The Metastore item entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(MetastoreItemInterface $entity);

  /**
   * Unsets the language for all Metastore item with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
