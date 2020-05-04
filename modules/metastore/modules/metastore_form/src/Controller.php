<?php

namespace Drupal\metadata_form;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Component\Uuid\Php;
use Drupal\Core\Entity\EntityRepository;
use Drupal\Core\Messenger\MessengerTrait;

/**
 * Controller.
 */
class Controller extends ControllerBase {
  use MessengerTrait;

  /**
   * Uuid Service.
   *
   * @var \Drupal\Component\Uuid\Php
   */
  protected $uuidService;

  /**
   * EntityRepository Service.
   *
   * @var \Drupal\Core\Entity\EntityRepository
   */
  protected $entityRepositoryService;

  /**
   * Class constructor.
   */
  public function __construct(Php $uuid, EntityRepository $entity_repository) {
    $this->uuidService = $uuid;
    $this->entityRepositoryService = $entity_repository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this service class.
    return new static(
      // Load the service required to construct this class.
      $container->get('uuid'),
      $container->get('entity.repository')
    );
  }

  /**
   * Page.
   */
  public function pageNew() {
    // Generate a uuid for new datasets, pass it to the form.
    $generated_uuid = $this->uuidService->generate();

    return [
      '#markup' => '<div id="app"></div>',
      '#attached' => [
        'library' => [
          'metadata_form/metadata_form',
        ],
        'drupalSettings' => [
          'tempUUID' => $generated_uuid,
          'isNew' => TRUE,
        ],
      ],
    ];
  }

  /**
   * Dataset edit.
   */
  public function pageEdit($uuid) {
    // Check if uuid corresponds to a dataset node.
    $node = $this->entityRepositoryService->loadEntityByUuid('node', $uuid);
    if ($node && $node->field_data_type->value === 'dataset') {
      return [
        '#markup' => '<div id="app"></div>',
        '#attached' => [
          'library' => [
            'metadata_form/metadata_form',
          ],
          'drupalSettings' => [
            'tempUUID' => $uuid,
            'isNew' => FALSE,
          ],
        ],
      ];
    }
    else {
      $this->messenger()->addWarning('No dataset with the UUID ' . $uuid . ' could be found.');
      return $this->redirect('view.data_content_typeset_content.page_1');
    }
  }

}
