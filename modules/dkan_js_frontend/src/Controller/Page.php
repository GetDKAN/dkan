<?php

namespace Drupal\dkan_js_frontend\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\metastore\Exception\MissingObjectException;
use Drupal\metastore\MetastoreService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * The Page controller.
 */
class Page extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Metastore service.
   */
  private MetastoreService $metastoreService;

  /**
   * The request stack.
   */
  protected RequestStack $requestStack;

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
      $container->get('path.current'),
      $container->get('request_stack'),
    );
  }

  /**
   * Constructor.
   */
  public function __construct(MetastoreService $service, CurrentPathStack $current_path, RequestStack $request_stack) {
    $this->metastoreService = $service;
    $this->currentPath = $current_path;
    $this->requestStack = $request_stack;
  }

  /**
   * Returns a render-able array.
   */
  public function content() {
    // Checking for 404 prevents an infinite loop.
    if ($this->requestStack->getCurrentRequest()->query->get('_exception_statuscode') !== 404) {
      $this->handleInvalidDatasetId();
    }

    return [
      '#theme' => 'page__dkan_js_frontend',
    ];
  }

  /**
   * If a dataset with an invalid ID is being requested, throw a 404 error.
   */
  protected function handleInvalidDatasetId() {
    // Path should always have leading slash.
    // @see \Symfony\Component\HttpFoundation\Request::getPathInfo()
    // Match any path that equals or begins with /dataset/[ID].
    $dataset_path_match = '/^\/dataset\/(?P<id>[^\/]+)/';

    $path = $this->currentPath->getPath();

    if (preg_match($dataset_path_match, $path, $matches)) {
      try {
        $this->metastoreService->get('dataset', $matches['id']);
      }
      catch (MissingObjectException $exception) {
        throw new NotFoundHttpException();
      }
    }
  }

}
