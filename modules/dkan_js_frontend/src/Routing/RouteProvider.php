<?php

namespace Drupal\dkan_js_frontend\Routing;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\Query\QueryFactoryInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class.
 */
class RouteProvider {

  private $appRoot;
  private $entityQuery;
  private $configFactory;

  /**
   * Constructor.
   */
  public function __construct(string $appRoot, QueryFactoryInterface $entityQuery, ConfigFactoryInterface $configFactory) {
    $this->appRoot = $appRoot;
    $this->entityQuery = $entityQuery;
    $this->routes = $configFactory->get('dkan_js_frontend.config')->get('routes');
  }

  /**
   * Routes.
   */
  public function routes() {
    $routes = new RouteCollection();
    $this->addIndexPage($routes);
    $routes->addRequirements(['_access' => 'TRUE']);
    return $routes;
  }

  /**
   * Route Helper.
   *
   * @param string $path
   *   Path.
   * @param string $name
   *   Name.
   *
   * @return \Symfony\Component\Routing\Route
   *   Route.
   */
  private function routeHelper(string $path, string $name) : Route {
    $route = new Route(
      "/$path",
      [
        '_controller' => '\Drupal\dkan_js_frontend\Controller\Page::content',
        'name' => $name,
      ]
    );
    $route->setMethods(['GET']);
    return $route;
  }

  /**
   * Private. All routes tagged with dkan_js_frontend.
   *
   * This allows for select attachment of libraries.
   */
  private function addIndexPage(RouteCollection $routes) {
    $config_routes = $this->routes;
    foreach ($config_routes as $config_route) {
      $possible_page = explode(",", $config_route);
      $routes->add($possible_page[0], $this->routeHelper($possible_page[1], "dkan_js_frontend"));
    }
  }

}
