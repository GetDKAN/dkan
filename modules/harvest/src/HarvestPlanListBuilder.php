<?php

namespace Drupal\harvest;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;

/**
 * Provides a list controller for the harvest plan entity type.
 */
class HarvestPlanListBuilder extends EntityListBuilder {

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

    $build['summary']['#markup'] = $this->t('Total harvest plans: @total', ['@total' => $total]);
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
    /** @var \Drupal\harvest\HarvestPlanInterface $entity */
    $row['id'] = $entity->id();
    return $row + parent::buildRow($entity);
  }

}
