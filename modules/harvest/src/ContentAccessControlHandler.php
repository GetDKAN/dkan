<?php

declare(strict_types=1);

namespace Drupal\harvest;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines an access control handler for content entities.
 *
 * @see \Drupal\harvest\Entity\HarvestPlan
 */
class ContentAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritDoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    if ($admin_permission = $this->entityType->getAdminPermission()) {
      return AccessResult::allowedIfHasPermission($account, $admin_permission);
    }
    switch ($operation) {
      case 'delete':
      case 'view':
      case 'run':
        return AccessResult::allowedIfHasPermission(
          $account,
          $this->entityTypeId . '.' . $operation
        );

      case 'update':
        return AccessResult::allowedIfHasPermission(
          $account,
          'edit ' . $this->entityTypeId
        );

      default:
        return parent::checkAccess($entity, $operation, $account);
    }
  }

  /**
   * {@inheritDoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL): AccessResult {
    return AccessResult::allowedIfHasPermissions($account, [
      'create ' . $this->entityTypeId,
      $this->entityType->getAdminPermission(),
    ], 'OR');
  }

}
