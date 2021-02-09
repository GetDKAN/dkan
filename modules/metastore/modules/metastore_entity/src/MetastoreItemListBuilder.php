<?php

namespace Drupal\metastore_entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Metastore item entities.
 *
 * @ingroup metastore_entity
 */
class MetastoreItemListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Metastore item ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var \Drupal\metastore_entity\Entity\MetastoreItem $entity */
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.metastore_item.edit_form',
      ['metastore_item' => $entity->id()]
    );
    return $row + parent::buildRow($entity);
  }

}
