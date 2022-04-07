<?php

namespace Drupal\metastore_admin;

use Drupal\Core\Entity\RevisionLogInterface;

/**
 * A Helper Class to assist with the "hide current revision" bulk action.
 *
 * Based on the moderated_content_bulk_publish module.
 *
 * @codeCoverageIgnore
 */
class MetastoreAdminModeration {

  /**
   * Entity.
   *
   * @var entity
   */
  private $entity = NULL;

  /**
   * ID.
   *
   * @var id
   */
  private $id = 0;

  /**
   * Default is 0, unpublish.
   *
   * @var status
   */
  private $status = 0;

  /**
   * Constructor.
   */
  public function __construct($entity, $status) {
    $this->entity = $entity;
    if (!is_null($status)) {
      $this->status = $status;
    }
    $this->id = $this->entity->id();
  }

  /**
   * Hide (exclude from search) current revision.
   */
  public function hide() {
    $user = \Drupal::currentUser();

    $this->entity->set('moderation_state', 'hidden');
    if ($this->entity instanceof RevisionLogInterface) {
      $this->entity->setRevisionCreationTime(\Drupal::time()->getRequestTime());
      $msg = 'Bulk operation create hidden revision';
      $this->entity->setRevisionLogMessage($msg);
      $current_uid = \Drupal::currentUser()->id();
      $this->entity->setRevisionUserId($current_uid);
    }

    if ($user->hasPermission('use dkan_publishing transition hidden')) {
      $this->entity->save();
    }
    else {
      \Drupal::logger('moderated_content_bulk_publish')->notice(
        utf8_encode("Bulk hide not permitted, check permissions")
      );
    }

    return $this->entity;
  }

}
