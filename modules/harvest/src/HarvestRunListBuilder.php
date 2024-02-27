<?php

declare(strict_types = 1);

namespace Drupal\harvest;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Harvest\ResultInterpreter;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Provides a list controller for the harvest run entity type.
 */
final class HarvestRunListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader(): array {
    return [
      'run_id' => $this->t('Run ID'),
      'harvest_plan_id' => $this->t('Harvest Plan'),
      'processed' => $this->t('Processed'),
      'created' => $this->t('Created'),
      'updated' => $this->t('Updated'),
      'errors' => $this->t('Errors'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity): array {
    /** @var \Drupal\harvest\HarvestRunInterface $entity */
    $interpreter = new ResultInterpreter(
      json_decode($entity->get('data')->getString(), TRUE)
    );
    $entity_id = $entity->id();
    return [
      'run_id' => Link::fromTextAndUrl($entity_id, Url::fromRoute(
        'entity.harvest_run.canonical',
        ['harvest_run' => $entity_id],
      )),
      // @todo Add plan link here when the harvest_plan entity PR is merged.
      'harvest_plan_id' => $entity->get('harvest_plan_id')->getString(),
      'processed' => $interpreter->countProcessed(),
      'created' => $interpreter->countCreated(),
      'updated' => $interpreter->countUpdated(),
      'errors' => $interpreter->countFailed(),
    ];
  }

}
