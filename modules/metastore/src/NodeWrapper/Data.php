<?php

namespace Drupal\metastore\NodeWrapper;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\common\Exception\DataNodeLifeCycleEntityValidationException;
use Drupal\metastore\MetastoreItemInterface;
use Drupal\node\NodeInterface;

/**
 * MetastoreItem object that wraps a data node, provides additional methods.
 *
 * Generate these objects using the factory:
 * dkan.metastore.metastore_item_factory.
 *
 * @see \Drupal\metastore\NodeWrapper\NodeDataFactory::getInstance()
 */
class Data implements MetastoreItemInterface {

  /**
   * Node.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected EntityInterface $node;

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private EntityTypeManagerInterface $entityTypeManager;

  /**
   * Entity Node Storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  private EntityStorageInterface $nodeStorage;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   A Drupal entity. Must be a Data node.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity Type Manager service.
   *
   * @throws \Drupal\common\Exception\DataNodeLifeCycleEntityValidationException
   *   Thrown when the entity is not a Data node.
   */
  public function __construct(EntityInterface $entity, EntityTypeManagerInterface $entityTypeManager) {
    if (!static::validEntityType($entity)) {
      throw new DataNodeLifeCycleEntityValidationException('Entity must be a node of bundle data.');
    }
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
   *
   * @todo Needing to call fix() on every method seems like a code smell.
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
   * Check if the entity is one that can be wrapped by Data.
   *
   * Currently only node entities which are data bundles are allowed.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity to be wrapped by a Data wrapper instance.
   *
   * @return bool
   *   TRUE if the entity can be wrapped, FALSE otherwise.
   */
  public static function validEntityType(EntityInterface $entity): bool {
    return ($entity instanceof NodeInterface) && ($entity->bundle() == "data");
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
   *
   * @todo Why do we do this?
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
