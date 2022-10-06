<?php

namespace Drupal\Dkan\Workflow;

use Drupal\dkan\MetastoreItemInterface;
use Drupal\dkan\Storage\MetastoreEntityItemInterface;
use Drupal\dkan\Workflow\MetastoreWorkflowInterface;

class MetastoreEntityWorkflow implements MetastoreWorkflowInterface {
  /**
   * {@inheritdoc}
   */
  public function publish(MetastoreItemInterface $item): void {
    $this->setWorkflowState($item, 'published');
  }

  /**
   * {@inheritdoc}
   */
  public function archive(MetastoreItemInterface $item): void {
    $this->setWorkflowState($item, 'archived');
  }

  /**
   * Change the state of a metastore item.
   *
   * @param \Drupal\dkan\Storage\MetastoreEntityItemInterface $item
   *   Metastore it4em.
   * @param string $state
   *   Any workflow state that can be applied to a metastore entity.
   *
   * @return bool
   *   Whether or not an item was transitioned.
   */
  protected function setWorkflowState(MetastoreEntityItemInterface $item, string $state): void {
    $entity = $this->getEntityLatestRevision($uuid);

    if (!$entity) {
      throw new MissingObjectException("Error: {$uuid} not found.");
    }
    elseif ($state !== $entity->get('moderation_state')->getString()) {
      $entity->set('moderation_state', $state);
      $entity->save();
      return TRUE;
    }
    else {
      return FALSE;
    }
  }
}