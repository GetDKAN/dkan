<?php

namespace Drupal\harvest;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\harvest\Entity\HarvestRunRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a list controller for the harvest plan entity type.
 *
 * @todo Add operations for register, run, and deregister.
 */
class HarvestPlanListBuilder extends EntityListBuilder {

  /**
   * Harvest service.
   *
   * @var \Drupal\harvest\HarvestService
   */
  protected HarvestService $harvestService;

  /**
   * Harvest run repository service.
   *
   * @var \Drupal\harvest\Entity\HarvestRunRepository
   */
  protected HarvestRunRepository $runRepository;

  /**
   * {@inheritDoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    $builder = parent::createInstance($container, $entity_type);
    $builder->harvestService = $container->get('dkan.harvest.service');
    $builder->runRepository = $container->get('dkan.harvest.storage.harvest_run_repository');
    return $builder;
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build = parent::render();
    // Add our styles.
    $build['table']['#attributes']['class'][] = 'dashboard-harvests';
    $build['table']['#attached']['library'][] = 'harvest/style';

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

    // Default values for a row if there's no info.
    $row = [
      'harvest_link' => $entity->toLink($harvest_plan_id),
      'extract_status' => [
        'data' => 'REGISTERED',
        'class' => 'registered',
      ],
      'last_run' => 'never',
      'dataset_count' => 'unknown',
    ];

    if ($runId = $this->harvestService->getLastHarvestRunId($harvest_plan_id)) {
      // There is a run identifier, so we should get that info.
      $run = $this->runRepository->loadEntity($harvest_plan_id, $runId);
      $extract_status = $run->get('extract_status')->getString();
      $row['extract_status'] = [
        'data' => $extract_status,
        'class' => strtolower($extract_status),
      ];
      $row['last_run'] = date('m/d/y H:m:s T', $runId);
      $row['dataset_count'] = Link::fromTextAndUrl(
        (string) count($run->get('extracted_uuid')),
        Url::fromRoute(
          'datastore.datasets_import_status_dashboard',
          ['harvest_id' => $harvest_plan_id],
        )
      );
    }
    // Don't call parent::buildRow() because we don't want operations (yet).
    return $row;
  }

}
