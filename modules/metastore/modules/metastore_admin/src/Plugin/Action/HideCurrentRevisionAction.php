<?php

namespace Drupal\metastore_admin\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\moderated_content_bulk_publish\AdminModeration;
use Drupal\node\NodeInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Adds a VBO action for the published (hidden) state.
 *
 * If type is left empty, action will be selectable for all
 * entity types.
 *
 * @Action(
 *   id = "hide_current_revision_action",
 *   label = @Translation("Hide Current Revision"),
 *   type = "node",
 *   confirm = TRUE,
 * )
 */
class HideCurrentRevisionAction extends ActionBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {

    $user = \Drupal::currentUser();

    if ($user->hasPermission('use dkan_publishing transition hidden')) {
      \Drupal::logger('metastore_admin')->notice("Executing hide latest revision of " . $entity->label());

      $adminModeration = new AdminModeration($entity, NodeInterface::PUBLISHED);
      $entity = $adminModeration->hide();

      return sprintf('Example action (configuration: %s)', print_r($this->configuration, TRUE));
    }
    else {
      $this->t("You don't have access to execute this operation!");
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
