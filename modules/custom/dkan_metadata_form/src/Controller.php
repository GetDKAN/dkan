<?php

namespace Drupal\dkan_metadata_form;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Component\Uuid\Php;

/**
 * Controller.
 */
class Controller extends ControllerBase {

  /**
   * Uuid Service.
   *
   * @var \Drupal\Component\Uuid\Php
   */
  protected $uuidService;

  /**
   * Class constructor.
   */
  public function __construct(Php $uuid) {
    $this->uuidService = $uuid;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this service class.
    return new static(
      // Load the service required to construct this class.
      $container->get('uuid')
    );
  }

  /**
   * Page.
   */
  public function page() {
    // Generate a uuid for new datasets, pass it to the form.
    $uuid = $this->uuidService->generate();

    return [
      '#markup' => '<div id="app"></div>',
      '#attached' => [
        'library' => [
          'dkan_metadata_form/dkan_metadata_form',
        ],
        'drupalSettings' => [
          'tempUUID' => $uuid,
        ],
      ],
    ];
  }

}
