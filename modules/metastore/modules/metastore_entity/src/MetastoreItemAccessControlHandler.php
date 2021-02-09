<?php

namespace Drupal\metastore_entity;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Metastore item entity.
 *
 * @see \Drupal\metastore_entity\Entity\MetastoreItem.
 */
class MetastoreItemAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\metastore_entity\Entity\MetastoreItemInterface $entity */

    switch ($operation) {

      case 'view':

        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished metastore item entities');
        }


        return AccessResult::allowedIfHasPermission($account, 'view published metastore item entities');

      case 'update':

        return AccessResult::allowedIfHasPermission($account, 'edit metastore item entities');

      case 'delete':

        return AccessResult::allowedIfHasPermission($account, 'delete metastore item entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add metastore item entities');
  }


}
