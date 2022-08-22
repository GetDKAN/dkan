<?php

namespace Drupal\dkan\Storage;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\dkan\Exception\DataNodeLifeCycleEntityValidationException;
use Drupal\dkan\LoggerTrait;
use Drupal\Core\Entity\EntityInterface;
use Drupal\dkan\MetastoreItemInterface;
use Drupal\dkan\Storage\MetastoreEntityItemInterface;
use Drupal\node\NodeInterface;

/**
 * MetastoreItem object that wraps a data node, provides additional methods.
 */
class MetastoreNodeItem implements MetastoreEntityItemInterface {
  use LoggerTrait;

  /**
   * Node.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $node;

  /**
   * Referenced raw metadata string.
   *
   * @var string
   */
  protected $rawMetadata;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   A Drupal entity.
   *
   * @throws \Drupal\dkan\Exception\DataNodeLifeCycleEntityValidationException
   */
  public function __construct(EntityInterface $entity) {
    $this->validate($entity);
    $this->node = $entity;
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
   * Getter.
   */
  public function getModifiedDate() {
    return $this->node->getChangedTime();
  }

  /**
   * Getter.
   */
  public function getIdentifier() {
    return $this->node->uuid();
  }

  /**
   * The unaltered version of the metadata.
   */
  public function getRawMetadata() {
    if (isset($this->node->rawMetadata)) {
      return json_decode($this->node->rawMetadata);
    }
  }

  /**
   * Protected.
   */
  public function getDataType() {
    return $this->node->get('field_data_type')->value;
  }

  /**
   * Protected.
   */
  public function getMetadata(bool $dereferenced = TRUE): object {
    return json_decode($this->node->get('field_json_metadata')->getString());
  }

  /**
   * {@inheritdoc}
   */
  public function setMetadata(object $metadata): void {
    $this->node->set('field_json_metadata', json_encode($metadata));
  }

  /**
   * {@inheritdoc}
   */
  public function setIdentifier(string $identifier): void {
    $this->node->set('uuid', $identifier);
  }

  /**
   * Setter.
   */
  public function setTitle(string $title): void {
    $this->node->set('title', $title);
  }

  /**
   * {@inheritdoc}
   */
  public function isNew(): bool {
    return $this->node->isNew();
  }

  /**
   * Validate an entity for item storage.
   *
   * @param \Drupal\node\NodeInterface $entity
   *   An entity object, must be a node.
   *
   * @throws \Drupal\dkan\Exception\DataNodeLifeCycleEntityValidationException
   */
  private function validate(NodeInterface $entity): void {
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
   * Public factory method.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   A Drupal entity.
   *
   * @return static
   *   Instantiated Mastore
   */
  public static function create(ContentEntityInterface $entity): MetastoreItemInterface {
    return new static($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function entity(): ContentEntityInterface {
    return $this->node;
  }

}
