<?php

declare(strict_types = 1);

namespace Drupal\harvest;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
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
   */
  public function buildRow(EntityInterface $entity): array {
    /** @var \Drupal\harvest\HarvestRunInterface $entity */
    $interpreter = new ResultInterpreter($entity->toResult());
    $entity_id = $entity->id();
    $harvest_plan_id = $entity->get('harvest_plan_id')->getString();
    // Make a link to the harvest plan if it exists.
    if ($plan = $this->planStorage->load($harvest_plan_id)) {
      $harvest_plan_id = $plan->toLink($harvest_plan_id);
    }
    return [
      'run_id' => $entity->toLink($entity_id),
      'harvest_plan_id' => $harvest_plan_id,
      'processed' => $interpreter->countProcessed(),
      'created' => $interpreter->countCreated(),
      'updated' => $interpreter->countUpdated(),
      'errors' => $interpreter->countFailed(),
    ];
  }

}
