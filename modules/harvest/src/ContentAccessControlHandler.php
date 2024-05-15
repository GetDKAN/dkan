<?php

declare(strict_types=1);

namespace Drupal\harvest;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines an access control handler for content entities.
 */
class ContentAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account): AccessResult {
    return match($operation) {
      'view' => AccessResult::allowedIfHasPermissions($account, [
        'view ' . $this->entityTypeId,
        'administer ' . $this->entityTypeId,
      ], 'OR'),
      'update' => AccessResult::allowedIfHasPermissions($account, [
        'edit ' . $this->entityTypeId,
        'administer ' . $this->entityTypeId,
      ], 'OR'),
      'delete' => AccessResult::allowedIfHasPermissions($account, [
        'delete ' . $this->entityTypeId,
        'administer ' . $this->entityTypeId,
      ], 'OR'),
      default => AccessResult::neutral(),
    };
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL): AccessResult {
    return AccessResult::allowedIfHasPermissions($account, [
      'create ' . $this->entityTypeId,
      'administer ' . $this->entityTypeId,
    ], 'OR');
  }

}
