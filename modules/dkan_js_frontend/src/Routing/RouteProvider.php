<?php

namespace Drupal\dkan_js_frontend\Routing;

use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * DKAN JS frontend route provider service.
 */
class RouteProvider {

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
   * Routes added here are marked with a default property 'name' with a value
   * of 'dkan_js_frontend'. This allows for select attachment of libraries.
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
          'name' => 'dkan_js_frontend',
        ]
      );
      $route->setMethods(['GET']);
      $routes->add($possible_page[0], $route);
    }
  }

}
