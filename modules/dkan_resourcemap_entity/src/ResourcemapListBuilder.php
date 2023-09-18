<?php

namespace Drupal\dkan_resourcemap_entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;

/**
 * Provides a list controller for the resourcemap entity type.
 */
class ResourcemapListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build['table'] = parent::render();

    $total = $this->getStorage()
      ->getQuery()
      ->accessCheck(FALSE)
      ->count()
      ->execute();

    $build['summary']['#markup'] = $this->t('Total resourcemaps: @total', ['@total' => $total]);
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('ID');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\dkan_resourcemap_entity\ResourcemapInterface $entity */
    $row['id'] = $entity->toLink();
    return $row + parent::buildRow($entity);
  }

}
