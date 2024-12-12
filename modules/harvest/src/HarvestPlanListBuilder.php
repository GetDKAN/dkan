<?php

namespace Drupal\harvest;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\harvest\Entity\HarvestRunRepository;
use Harvest\ResultInterpreter;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a list controller for the harvest plan entity type.
 *
 * @todo Add operations for register, run, and deregister.
 */
class HarvestPlanListBuilder extends EntityListBuilder {

  /**
   * Harvest service.
   */
  protected HarvestService $harvestService;

  /**
   * Harvest run repository service.
   */
  protected HarvestRunRepository $harvestRunRepository;

  /**
   * Entity storage service for the harvest_run entity type.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected EntityStorageInterface $harvestRunStorage;

  /**
   * {@inheritDoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    $builder = parent::createInstance($container, $entity_type);
    $builder->harvestRunRepository = $container->get('dkan.harvest.storage.harvest_run_repository');
    $builder->harvestRunStorage = $container->get('entity_type.manager')->getStorage('harvest_run');
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
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    // Don't call parent::buildHeader() because we don't want operations (yet).
    return [
      'harvest_link' => $this->t('Harvest ID'),
      'extract_status' => $this->t('Extract Status'),
      'last_run' => $this->t('Last Run'),
      'dataset_count' => $this->t('# of Datasets'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\harvest\HarvestPlanInterface $entity */
    $harvest_plan_id = $entity->get('id')->getString();
    $run_entity = NULL;

    if ($run_id = $this->harvestRunRepository->getLastHarvestRunId($harvest_plan_id)) {
      // There is a run identifier, so we should get that info.
      /** @var \Drupal\harvest\HarvestRunInterface $run_entity */
      $run_entity = $this->harvestRunStorage->load($run_id);
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
    if ($run_entity) {
      $extract_status = $run_entity->get('extract_status')->getString();
      $interpreter = new ResultInterpreter($run_entity->toResult());
      $row['extract_status'] = [
        'data' => $extract_status,
        'class' => strtolower($extract_status),
      ];
      $row['last_run'] = date('m/d/y H:m:s T', $run_entity->get('timestamp')->value);
      $row['dataset_count'] = $interpreter->countProcessed();
    }
    // Don't call parent::buildRow() because we don't want operations (yet).
    return $row;
  }

}
