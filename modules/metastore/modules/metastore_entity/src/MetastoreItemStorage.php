<?php

namespace Drupal\metastore_entity;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\metastore_entity\Entity\MetastoreItemEntityInterface;

/**
 * Defines the storage handler class for Metastore item entities.
 *
 * This extends the base storage class, adding required special handling for
 * Metastore item entities.
 *
 * @ingroup metastore_entity
 */
class MetastoreItemStorage extends SqlContentEntityStorage implements MetastoreItemStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(MetastoreItemEntityInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {metastore_item_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {metastore_item_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(MetastoreItemEntityInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {metastore_item_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('metastore_item_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
