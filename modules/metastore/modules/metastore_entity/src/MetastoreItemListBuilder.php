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
    $header['title'] = $this->t('Title');
    $header['id'] = $this->t('Identifier');
    $header['schema'] = $this->t('Metastore Schema');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var \Drupal\metastore_entity\Entity\MetastoreItem $entity */
    $row['title'] = Link::createFromRoute(
      $entity->label(),
      'entity.metastore_item.edit_form',
      ['metastore_item' => $entity->id()]
    );
    $row['uuid'] = $entity->uuid();
    $row['schema'] = $entity->getSchemaId();
    return $row + parent::buildRow($entity);
  }

}
