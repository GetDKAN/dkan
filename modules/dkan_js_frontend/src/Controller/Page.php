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
  protected CurrentPathStack $currentPath;

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
    // Path should always have leading slash.
    // @see \Symfony\Component\HttpFoundation\Request::getPathInfo()
    // Dataset path is /dataset/[ID]/data.
    $dataset_data_path_match = '/^\/dataset\/(?P<id>[^\/]+)\/data$/';
    // Dataset path is /dataset/[ID].
    $dataset_path_match = '/^\/dataset\/(?P<id>[^\/]+)$/';

    $path = $this->currentPath->getPath();

    if (preg_match($dataset_data_path_match, $path, $matches)
      || preg_match($dataset_path_match, $path, $matches)) {

      try {
        $this->service->get('dataset', $matches['id']);
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
