<?php

namespace Drupal\metastore\NodeWrapper;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\common\Exception\DataNodeLifeCycleEntityValidationException;
use Drupal\metastore\MetastoreItemInterface;
use Drupal\node\Entity\Node;

/**
 * MetastoreItem object that wraps a data node, provides additional methods.
 */
class Data implements MetastoreItemInterface {

  /**
   * Node.
   *
   * @var \Drupal\node\Entity\Node
   */
  protected $node;

  /**
   * Referenced raw metadata string.
   *
   * @var string
   */
  protected $rawMetadata;

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  private $entityTypeManager;

  /**
   * Entity Node Storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  private $nodeStorage;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   A Drupal entity.
   * @param \Drupal\Core\Entity\EntityTypeManager $entityTypeManager
   *   Entity Type Manager service.
   *
   * @throws \Drupal\common\Exception\DataNodeLifeCycleEntityValidationException
   */
  public function __construct(EntityInterface $entity, EntityTypeManager $entityTypeManager) {
    $this->validate($entity);
    $this->node = $entity;
    $this->entityTypeManager = $entityTypeManager;
    $this->nodeStorage = $this->entityTypeManager->getStorage('node');
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return $this->node->getCacheContexts();
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return $this->node->getCacheTags();
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return $this->node->getCacheMaxAge();
  }

  /**
   * Private.
   */
  private function fix() {
    $this->fixDataType();
    $this->saveRawMetadata();
  }

  /**
   * Getter.
   */
  public function getModifiedDate() {
    $this->fix();
    // Use revision date because the latest revision date does not
    // match the node changed value when there are multiple drafts.
    return $this->node->getRevisionCreationTime();
  }

  /**
   * Getter.
   */
  public function getIdentifier() {
    $this->fix();

    return $this->node->uuid();
  }

  /**
   * The unaltered version of the metadata.
   */
  public function getRawMetadata() {
    $this->fix();
    if (isset($this->node->rawMetadata)) {
      return json_decode($this->node->rawMetadata);
    }
  }

  /**
   * Protected.
   */
  public function getDataType() {
    $this->fix();
    return $this->node->get('field_data_type')->value;
  }

  /**
   * Protected.
   */
  public function getMetaData() {
    $this->fix();
    return json_decode($this->node->get('field_json_metadata')->getString());
  }

  /**
   * Protected.
   */
  public function setMetadata($metadata) {
    $this->fix();
    $this->node->set('field_json_metadata', json_encode($metadata));
  }

  /**
   * Setter.
   */
  public function setIdentifier($identifier) {
    $this->fix();
    $this->node->set('uuid', $identifier);
  }

  /**
   * Setter.
   */
  public function setTitle($title) {
    $this->fix();
    $this->node->set('title', $title);
  }

  /**
   * Is New.
   */
  public function isNew() {
    return $this->node->isNew();
  }

  /**
   * Private.
   */
  private function validate(EntityInterface $entity) {
    if (!($entity instanceof Node)) {
      throw new DataNodeLifeCycleEntityValidationException("We only work with nodes.");
    }

    if ($entity->bundle() != "data") {
      throw new DataNodeLifeCycleEntityValidationException("We only work with data nodes.");
    }
  }

  /**
   * Private.
   */
  private function fixDataType() {
    if (empty($this->node->get('field_data_type')->getString())) {
      $this->node->set('field_data_type', 'dataset');
    }
  }

  /**
   * Protected.
   */
  public function getSchemaId() {
    $this->fix();
    return $this->node->get('field_data_type')->getString();
  }

  /**
   * Private.
   */
  private function saveRawMetadata() {
    // Temporarily save the raw json metadata, for later use.
    if (!isset($this->node->rawMetadata)) {
      $raw = $this->node->get('field_json_metadata')->value;
      $this->node->rawMetadata = $raw;
    }
  }

  /**
   * Get the latest revision ID.
   *
   * @return int|string|null
   *   Latest revision ID or null
   */
  public function getLoadedRevisionId() {
    return $this->node->getLoadedRevisionId();
  }

  /**
   * Get the current revision ID.
   *
   * @return int|mixed|string|null
   *   Revision ID or null
   */
  public function getRevisionId() {
    return $this->node->getRevisionId();
  }

  /**
   * Get latest revision.
   *
   * @return Data|void
   *   Data object containing the latest revision or null
   *
   * @throws \Drupal\common\Exception\DataNodeLifeCycleEntityValidationException
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getLatestRevision() {
    if (!$this->isNew()) {
      // See https://www.drupal.org/project/drupal/issues/3201209
      // node->original is set to the published revision, not the latest.
      // Compare to the latest revision of the node instead.
      $latest_revision_id = $this->getLoadedRevisionId();
      $original = $this->nodeStorage->loadRevision($latest_revision_id);
      return new Data($original, $this->entityTypeManager);
    }
  }

  /**
   * Get published revision.
   *
   * @return Data|void
   *   Data object containing the latest revision or null
   *
   * @throws \Drupal\common\Exception\DataNodeLifeCycleEntityValidationException
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getPublishedRevision() {
    if (!$this->isNew()) {
      $node = $this->nodeStorage->load($this->node->id());
      if ($node->isPublished()) {
        return new Data($node, $this->entityTypeManager);
      }
    }
  }

  /**
   * Get moderation state.
   *
   * @return string
   *   Node moderation state
   */
  public function getModerationState() {
    return $this->node->get('moderation_state')->getString();
  }

  /**
   * Getter.
   *
   * @deprecated Use getLatestRevision() instead.
   *
   * @see https://www.drupal.org/project/drupal/issues/3346430
   */
  public function getOriginal() {
    if (isset($this->node->original)) {
      return new Data($this->node->original);
    }
  }

  /**
   * Save the "wrapped" node.
   *
   * Useful for some operations - usually recommended to use the metastore
   * service's POST and PUT functions rather than saving the node directly.
   */
  public function save() {
    $this->node->save();
  }

}
