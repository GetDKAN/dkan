<?php

namespace Drupal\harvest\Form;

use Drupal\Core\Entity\ContentEntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\harvest\Entity\HarvestRunRepository;
use Drupal\harvest\HarvestService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Confirmation form for when you want to run a harvest plan.
 */
class HarvestPlanRunForm extends ContentEntityConfirmFormBase {

  /**
   * Harvest service.
   *
   * @var \Drupal\harvest\HarvestService
   */
  protected HarvestService $harvestService;

  /**
   * Harvest run entity repository.
   *
   * @var \Drupal\harvest\Entity\HarvestRunRepository
   */
  protected HarvestRunRepository $harvestRunRepository;

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    $form = parent::create($container);
    $form->harvestService = $container->get('dkan.harvest.service');
    $form->harvestRunRepository = $container->get('dkan.harvest.storage.harvest_run_repository');

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to run the harvest plan %name?', ['%name' => $this->entity->label()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return $this->entity->toUrl('collection');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Run');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $plan_id = (string) $this->entity->id();

    // @todo Handle exceptions.
    $result = $this->harvestService->runHarvest($plan_id);

    $harvest_run = $this->harvestRunRepository->loadEntity($plan_id, $result['identifier']);
    $form_state->setRedirectUrl($harvest_run->toUrl());
  }

}
