<?php

namespace Drupal\metastore\NodeWrapper;

use Drupal\common\Exception\DataNodeLifeCycleEntityValidationException;
use Drupal\common\LoggerTrait;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\metastore\MetastoreItemInterface;
use Drupal\node\Entity\Node;

/**
 * MetastoreItem object that wraps a data node, provides additional methods.
 */
class Data implements MetastoreItemInterface {
  use LoggerTrait;

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
   * @var Drupal\Core\Entity\EntityTypeManager
   */
  private $entityTypeManager;

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
    $cacheTags = $this->node->getCacheTags();
    return $cacheTags;
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
    $schemaId = $this->node->get('field_data_type')->getString();
    return $schemaId;
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
   * Getter.
   */
  public function getOriginal() {
    if (!$this->isNew()) {
      // See https://www.drupal.org/project/drupal/issues/3201209
      // node->original is set to the published revision, not the latest.
      // Compare to the latest revision of the node instead.
      $node_storage = $this->entityTypeManager->getStorage('node');
      $latest_revision_id = $node_storage->getLatestRevisionId($this->node->id());
      $original = $node_storage->loadRevision($latest_revision_id);
      return new Data($original, $this->entityTypeManager);
    }
  }

}
