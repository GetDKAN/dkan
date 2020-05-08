<?php

namespace Drupal\common;

use Drupal\Core\Entity\EntityInterface;
use Drupal\common\Exception\DataNodeLifeCycleEntityValidationException;
use Drupal\node\Entity\Node;

/**
 * DataNodeLifeCycle.
 */
class AbstractDataNodeLifeCycle {
  protected $node;

  /**
   * Constructor.
   */
  public function __construct(EntityInterface $entity) {
    $this->validate($entity);
    $this->node = $entity;
  }

  /**
   * Protected.
   */
  protected function getDataType() {
    return $this->node->get('field_data_type')->value;
  }

  /**
   * Protected.
   */
  protected function setDataType($type) {
    $this->node->set('field_data_type', $type);
  }

  /**
   * Protected.
   */
  protected function getMetaData() {
    /* @var $entity \Drupal\node\Entity\Node */
    $entity = $this->node;
    return json_decode($entity->get('field_json_metadata')->value);
  }

  /**
   * Protected.
   */
  protected function setMetadata($metadata) {
    /* @var $entity \Drupal\node\Entity\Node */
    $entity = $this->node;
    $entity->set('field_json_metadata', json_encode($metadata));
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

}
