<?php

namespace Drupal\harvest;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a list controller for the harvest plan entity type.
 */
class HarvestPlanListBuilder extends EntityListBuilder {

  /**
   * Harvest service.
   *
   * @var \Drupal\harvest\HarvestService
   */
  protected HarvestService $harvestService;

  /**
   * {@inheritDoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    $builder = parent::createInstance($container, $entity_type);
    $builder->harvestService = $container->get('dkan.harvest.service');
    return $builder;
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build['table'] = parent::render();
    // Add our styles.
    $build['table']['table']['#attributes']['class'][] = 'dashboard-harvests';
    $build['table']['table']['#attached']['library'][] = 'harvest/style';

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
    $header = [
      'harvest_link' => $this->t('Harvest ID'),
      'extract_status' => $this->t('Extract Status'),
      'last_run' => $this->t('Last Run'),
      'dataset_count' => $this->t('# of Datasets'),
    ];
    // Don't call parent::buildHeader() because we don't want operations (yet).
    return $header;
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\harvest\HarvestPlanInterface $entity */
    $harvest_plan_id = $entity->get('id')->getString();

    if ($runId = $this->harvestService->getLastHarvestRunId($harvest_plan_id)) {
      // There is a run identifier, so we should get that info.
      $info = json_decode($this->harvestService->getHarvestRunInfo($harvest_plan_id, $runId));
    }

    // Default values for a row if there's no info.
    $row = [
      'harvest_link' => Link::fromTextAndUrl($harvest_plan_id, Url::fromRoute(
        'datastore.datasets_import_status_dashboard',
        ['harvest_id' => $harvest_plan_id],
      )),
      'extract_status' => [
        'data' => 'REGISTERED',
        'class' => 'registered',
      ],
      'last_run' => 'never',
      'dataset_count' => 'unknown',
    ];
    // Add stats if there is info for it.
    if ($info ?? FALSE) {
      $row['extract_status'] = [
        'data' => $info->status->extract,
        'class' => strtolower($info->status->extract),
      ];
      $row['last_run'] = date('m/d/y H:m:s T', $runId);
      $row['dataset_count'] = count(array_keys((array) $info->status->load));
    }
    // Don't call parent::buildRow() because we don't want operations (yet).
    return $row;
  }

}
