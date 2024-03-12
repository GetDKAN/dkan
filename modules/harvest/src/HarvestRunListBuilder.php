<?php

declare(strict_types = 1);

namespace Drupal\harvest;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Harvest\ResultInterpreter;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a list controller for the harvest run entity type.
 */
final class HarvestRunListBuilder extends EntityListBuilder {

  protected EntityStorageInterface $planStorage;

  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    $builder = parent::createInstance($container, $entity_type);
    $builder->planStorage = $container->get('entity_type.manager')->getStorage('harvest_plan');
    return $builder;
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader(): array {
    return [
      'run_id' => $this->t('Harvest Run ID'),
      'harvest_plan_id' => $this->t('Harvest Plan'),
      'processed' => $this->t('# Processed'),
      'created' => $this->t('# Created'),
      'updated' => $this->t('# Updated'),
      'errors' => $this->t('# Errors'),
    ];
  }

  /**
   * {@inheritdoc}
   *
   * @todo Link to plan and run from their IDs when we figure out UX.
   */
  public function buildRow(EntityInterface $entity): array {
    /** @var \Drupal\harvest\HarvestRunInterface $entity */
    $interpreter = new ResultInterpreter($entity->toResult());
    $entity_id = $entity->id();
    $harvest_plan_id = $entity->get('harvest_plan_id')->getString();
    return [
      'run_id' => $entity_id,
      'harvest_plan_id' => $harvest_plan_id,
      'processed' => $interpreter->countProcessed(),
      'created' => $interpreter->countCreated(),
      'updated' => $interpreter->countUpdated(),
      'errors' => $interpreter->countFailed(),
    ];
  }

}
