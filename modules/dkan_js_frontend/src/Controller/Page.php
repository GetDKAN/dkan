<?php

namespace Drupal\dkan_js_frontend\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\metastore\Exception\MissingObjectException;
use Drupal\metastore\MetastoreService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * The Page controller.
 */
class Page extends ControllerBase {

  /**
   * Metastore service.
   */
  private MetastoreService $service;

  /**
   * The current path.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $currentPath;

  /**
   * Inherited.
   *
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('dkan.metastore.service'),
      $container->get('path.current')
    );
  }

  /**
   * Constructor.
   */
  public function __construct(MetastoreService $service, CurrentPathStack $current_path) {
    $this->service = $service;
    $this->currentPath = $current_path;
  }

  /**
   * Returns a render-able array.
   */
  public function content() {
    $path_parts = explode('/', $this->currentPath->getPath());

    if (is_array($path_parts) && count($path_parts) === 3 && $path_parts[1] === 'dataset') {
      try {
        $this->service->get('dataset', $path_parts[2]);
      }
      catch (MissingObjectException $exception) {
        throw new NotFoundHttpException();
      }
    }

    return [
      '#theme' => 'page__dkan_js_frontend',
    ];
  }

}
