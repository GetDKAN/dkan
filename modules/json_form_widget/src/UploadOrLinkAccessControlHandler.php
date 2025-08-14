<?php

namespace Drupal\json_form_widget;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\file\FileAccessControlHandler;

/**
 * Override file access to let editors access remote files in UploadOrLink.
 */
class UploadOrLinkAccessControlHandler extends FileAccessControlHandler {

  /**
   * Override file 'download' file access for linked files.
   *
   * @see: Drupal\file\FileAccessControlHandler
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    if ($operation == 'download' &&
        \Drupal::service('stream_wrapper_manager')->getScheme($entity->getFileUri()) === 'https') {
          return AccessResult::allowed();
    }
    else {
      return parent::checkAccess($entity, $operation, $account);
    }
  }

}
