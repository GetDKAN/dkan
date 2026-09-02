<?php

namespace Drupal\dkan_metastore\NodeWrapper;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\dkan_common\Exception\DataNodeLifeCycleEntityValidationException;
use Drupal\dkan_metastore\MetastoreItemInterface;
use Drupal\node\NodeInterface;

/**
 * MetastoreItem object that wraps a data node, provides additional methods.
 *
 * Generate these objects using the factory:
 * dkan.metastore.metastore_item_factory.
 *
 * @see \Drupal\dkan_metastore\NodeWrapper\NodeDataFactory::getInstance()
 */
class Data implements MetastoreItemInterface {

  /**
   * The node field name for the metadata JSON string.
   */
  private const JSON_METADATA_FIELD = 'field_json_metadata';

  /**
   * The field name for determining the schema/data type of the node.
   */
  private const DATA_TYPE_FIELD = 'field_data_type';

  /**
   * The default data type for nodes that don't have one yet.
   */
  private const DEFAULT_DATA_TYPE = 'dataset';

  /**
   * The data node we're wrapping.
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
   * @throws \Drupal\dkan_common\Exception\DataNodeLifeCycleEntityValidationException
   *   Thrown when the entity is not a Data node.
   */
  public function __construct(EntityInterface $entity, EntityTypeManagerInterface $entityTypeManager) {
    if (!static::validEntityType($entity)) {
      throw new DataNodeLifeCycleEntityValidationException('Entity must be a node of bundle data.');
    }
    $this->node = $entity;
    $this->entityTypeManager = $entityTypeManager;
    $this->nodeStorage = $this->entityTypeManager->getStorage('node');

    // If our node is a new creation or otherwise doesn't have a type yet, we
    // provide it with a default one.
    if ($this->node->get(self::DATA_TYPE_FIELD)->isEmpty()) {
      $this->node->set(self::DATA_TYPE_FIELD, self::DEFAULT_DATA_TYPE);
    }
    // Stash the 'raw' metadata if it's not already there.
    if (!isset($this->node->rawMetadata)) {
      $this->node->rawMetadata = $this->node->get(self::JSON_METADATA_FIELD)->value;
    }
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
   * Getter.
   */
  public function getModifiedDate() {
    // Use revision date because the latest revision date does not
    // match the node changed value when there are multiple drafts.
    return $this->node->getRevisionCreationTime();
  }

  /**
   * Getter.
   */
  public function getIdentifier() {
    return $this->node->uuid();
  }

  /**
   * {@inheritDoc}
   */
  public function getRawMetadata() {
    if (isset($this->node->rawMetadata)) {
      return json_decode($this->node->rawMetadata);
    }
  }

  /**
   * Get the schema name for the Data node.
   *
   * @returns string
   */
  public function getDataType() {
    return $this->node->get('field_data_type')->value;
  }

  /**
   * {@inheritDoc}
   */
  public function getMetadata() {
    return json_decode($this->node->get(self::JSON_METADATA_FIELD)->getString());
  }

  /**
   * {@inheritDoc}
   */
  public function setMetadata($metadata) {
    $this->node->set(self::JSON_METADATA_FIELD, json_encode($metadata));
  }

  /**
   * Setter.
   */
  public function setIdentifier($identifier) {
    $this->node->set('uuid', $identifier);
  }

  /**
   * Setter.
   */
  public function setTitle($title) {
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
   * Protected.
   */
  public function getSchemaId() {
    return $this->getEntity()->get('field_data_type')->getString();
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
   * @throws \Drupal\dkan_common\Exception\DataNodeLifeCycleEntityValidationException
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
   * @throws \Drupal\dkan_common\Exception\DataNodeLifeCycleEntityValidationException
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
   * Save the "wrapped" node.
   *
   * Useful for some operations - usually recommended to use the metastore
   * service's POST and PUT functions rather than saving the node directly.
   */
  public function save() {
    $this->node->save();
  }

  /**
   * Get the node entity.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The wrapped node entity.
   */
  public function getEntity() {
    return $this->node;
  }

}
