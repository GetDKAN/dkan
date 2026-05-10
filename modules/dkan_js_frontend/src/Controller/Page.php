<?php

namespace Drupal\dkan_js_frontend\Controller;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Http\Exception\CacheableNotFoundHttpException;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\dkan_js_frontend\Routing\RouteProvider;
use Drupal\dkan_metastore\Exception\MissingObjectException;
use Drupal\dkan_metastore\NodeWrapper\NodeDataFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Page controller.
 *
 * Routes defined in the dkan_js_frontend.config.routes configuration use this
 * controller.
 *
 * For datastore routes, we check if the datastore identifier is valid, and if
 * not throw an exception signaling a 404 response.
 */
class Page implements ContainerInjectionInterface {

  /**
   * Config for dkan_js_frontend.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected readonly ImmutableConfig $frontendConfig;

  /**
   * Node data factory service.
   *
   * @var \Drupal\dkan_metastore\NodeWrapper\NodeDataFactory
   */
  protected readonly NodeDataFactory $nodeDataFactory;

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('dkan.metastore.metastore_item_factory'),
    );
  }

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Config factory service.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   Renderer service.
   * @param \Drupal\dkan_metastore\NodeWrapper\NodeDataFactory $nodeDataFactory
   *   Node data factory service.
   */
  public function __construct(
    ConfigFactoryInterface $configFactory,
    NodeDataFactory $nodeDataFactory,
  ) {
    $this->frontendConfig = $configFactory->get('dkan_js_frontend.config');
    $this->nodeDataFactory = $nodeDataFactory;
  }

  /**
   * Make a renderable page.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   Route match for this request.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   This request.
   *
   * @return array
   *   Render array.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
   *   Throws a not-found exception for dataset routes which have an invalid
   *   identifier.
   */
  public function content(RouteMatchInterface $route_match, Request $request) {
    $cacheable_metadata = (new CacheableMetadata())
      // Keep as long as possible. In practice, this will probably be
      // overridden by other theming elements during render.
      ->setCacheMaxAge(Cache::PERMANENT)
      // Allow cache invalidation if we change config for this module.
      ->addCacheableDependency($this->frontendConfig);
    $dataset_node = NULL;
    // Check for valid dataset identifier on the dataset route.
    // Checking for 404 prevents an infinite loop from the 404 we might cause
    // later.
    // @todo Make the dataset and dataset API routes a special case.
    if (
      in_array($route_match->getRouteName(), [
        RouteProvider::ROUTE_PREFIX . 'dataset',
        RouteProvider::ROUTE_PREFIX . 'datasetapi'
      ]) &&
      ($request->query->get('_exception_statuscode') !== 404)
    ) {
      try {
        // Allow cache invalidation if our dataset module changes at all.
        // @todo Ideally DKAN would give us a way to also get cacheability
        //   metadata from other related nodes, such as distribution.
        $cacheable_metadata->addCacheableDependency($dataset_node);
        // @todo Figure out a way to find if the identifier exists without
        //   triggering the lifecycle loading of tertiary nodes, etc.
        $dataset_node = $this->nodeDataFactory->getInstance(
          $route_match->getRawParameter('id') ?? ''
        );
      }
      // Handle if the dataset does not exist.
      catch (MissingObjectException) {
        // Throw an exception that tells Drupal send back a 404. Also enforce
        // the same caching, so that we won't ever query the DB until the cache
        // is invalidated.
        throw new CacheableNotFoundHttpException($cacheable_metadata);
      }
    }

    $build = [
      '#theme' => 'page__dkan_js_frontend',
    ];
    $cacheable_metadata->applyTo($build);

    return $build;
  }

}
