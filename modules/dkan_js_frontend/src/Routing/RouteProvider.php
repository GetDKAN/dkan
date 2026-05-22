<?php

namespace Drupal\dkan_js_frontend\Routing;

use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * DKAN JS frontend route provider service.
 */
class RouteProvider {

  const ROUTE_PREFIX = 'dkan_js_frontend.';

  /**
   * Route-URL pairs, separated by a comma.
   *
   * @var string[]
   */
  protected $configRouteMap;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Config factory service.
   */
  public function __construct(ConfigFactoryInterface $configFactory) {
    $this->configRouteMap = $configFactory->get('dkan_js_frontend.config')->get('routes') ?? [];
  }

  /**
   * Provide routes derived from configuration.
   *
   * @return \Symfony\Component\Routing\RouteCollection
   *   Collection of routes derived from configuration.
   */
  public function routes(): RouteCollection {
    $routes = new RouteCollection();
    $this->addRoutesFromConfig($routes);
    // @todo Either create an access controller or perform some access checking
    //   in the controller.
    $routes->addRequirements(['_access' => 'TRUE']);
    return $routes;
  }

  /**
   * Add all the routes specified in configuration.
   *
   * Routes added here are marked with a default property
   * '_is_dkan_js_frontend' with a value of 'true'. This allows for select
   * attachment of libraries.
   *
   * @param \Symfony\Component\Routing\RouteCollection $routes
   *   The collection to add config routes to.
   *
   * @see dkan_js_frontend_page_attachments()
   */
  private function addRoutesFromConfig(RouteCollection $routes): void {
    foreach ($this->configRouteMap as $config_route) {
      $possible_page = explode(',', $config_route);
      $route = new Route(
        '/' . $possible_page[1],
        [
          '_controller' => '\Drupal\dkan_js_frontend\Controller\Page::content',
          '_is_dkan_js_frontend' => 'true',
        ],
      );
      $route->setMethods(['GET']);
      $route->addRequirements([
        '_permission' => 'access content',
      ]);
      $routes->add(self::ROUTE_PREFIX . $possible_page[0], $route);
    }
  }

}
