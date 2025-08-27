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
    $file_scheme = \Drupal::service('stream_wrapper_manager')->getScheme($entity->getFileUri());
    if ($operation == 'download' &&
      str_starts_with($file_scheme, 'http')) {
      return AccessResult::allowed();
    }
    else {
      return parent::checkAccess($entity, $operation, $account);
    }
  }

}
