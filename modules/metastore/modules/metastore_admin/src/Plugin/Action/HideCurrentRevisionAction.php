<?php

namespace Drupal\metastore_admin\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\metastore_admin\MetastoreAdminModeration;
use Drupal\node\NodeInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * An example action covering most of the possible options.
 *
 * If type is left empty, action will be selectable for all
 * entity types.
 *
 * @Action(
 *   id = "hide_current_revision_action",
 *   label = @Translation("Archive Current Revision"),
 *   type = "node",
 *   confirm = TRUE,
 * )
 *
 * @codeCoverageIgnore
 */
class HideCurrentRevisionAction extends ActionBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {

    $user = \Drupal::currentUser();

    if ($user->hasPermission('moderated content bulk archive')) {
      \Drupal::logger('moderated_content_bulk_publish')->notice("Executing hide latest revision of " . $entity->label());

      $adminModeration = new MetastoreAdminModeration($entity, NodeInterface::PUBLISHED);
      $entity = $adminModeration->hide();

      // Check if published.
      if (!$entity->isPublished()) {
        $msg = "Something went wrong, the entity must be published by this point.  Review your content moderation configuration make sure you have the hidden state available and try again.";
        \Drupal::Messenger()->addError(utf8_encode($msg));
        \Drupal::logger('moderated_content_bulk_publish')->warning($msg);
        return $msg;
      }
      return sprintf('Example action (configuration: %s)', print_r($this->configuration, TRUE));
    }
    else {
      \Drupal::messenger()->addWarning($this->t("You don't have access to execute this operation!"));
      return;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    if ($object->getEntityType() === 'node') {
      $access = $object->access('update', $account, TRUE)
        ->andIf($object->status->access('edit', $account, TRUE));
      return $return_as_object ? $access : $access->isAllowed();
    }

    return TRUE;
  }

}
