<?php

namespace Drupal\dkan_metastore\ContentModeration;

use Drupal\content_moderation\ModerationInformationInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\dkan_metastore\Storage\NodeData;

/**
 * Service to help out with content moderation stuff.
 */
class ContentModerationHelper {

  /**
   * Content moderation info service.
   *
   * @var \Drupal\content_moderation\ModerationInformationInterface
   */
  private readonly ModerationInformationInterface $moderationInformation;

  /**
   * Entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private readonly EntityTypeManagerInterface $entityTypeManager;

  public function __construct(
    ModerationInformationInterface $moderationInformation,
    EntityTypeManagerInterface $entityTypeManager,
  ) {
    $this->moderationInformation = $moderationInformation;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Query for entity IDs that match the given moderation states.
   *
   * @param array $moderation_states
   *   Array of moderation state IDs to include. Example: ['published'].
   * @param array $within_ids
   *   (Optional) Entity ids to search within. These will be part of an 'IN'
   *   query. Default is to return all entity ids for the given moderation
   *   states.
   *
   * @return array
   *   Entity IDs for the entities we found.
   */
  public function entityIdsForWorkflowStates(array $moderation_states, array $within_ids = []): array {
    $cm_storage = $this->entityTypeManager
      ->getStorage('content_moderation_state');
    $cm_query = $cm_storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('content_entity_type_id', NodeData::ENTITY_TYPE)
      ->condition('moderation_state', $moderation_states, 'IN');
    if ($within_ids) {
      $cm_query->condition('id', $within_ids, 'IN');
    }
    return array_map(function ($entity) {
      return $entity->get('id')->getString();
    }, array_values($cm_storage->loadMultiple($cm_query->execute())));
  }

  /**
   * Get the workflow states configured for this site.
   *
   * @return \Drupal\workflows\StateInterface[]
   *   An array of workflow states, keyed by state IDs.
   */
  public function getWorkflowStates(): array {
    $states = $this->moderationInformation
      ->getWorkflowForEntityTypeAndBundle(NodeData::ENTITY_TYPE, NodeData::BUNDLE)
      ->getTypePlugin()
      ->getStates();
    return $states;
  }

}
