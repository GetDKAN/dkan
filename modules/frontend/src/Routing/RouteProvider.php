<?php

namespace Drupal\frontend\Routing;

use Drupal\Core\Entity\Query\QueryFactoryInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class.
 */
class RouteProvider {

  private $appRoot;
  private $entityQuery;

  /**
   * Constructor.
   */
  public function __construct(string $appRoot, QueryFactoryInterface $entityQuery) {
    $this->appRoot = $appRoot;
    $this->entityQuery = $entityQuery;
  }

  /**
   * Routes.
   */
  public function routes() {
    $routes = new RouteCollection();
    $package_json = file_get_contents($this->appRoot . "/frontend/package.json");
    $decode_package = json_decode($package_json, true);
    if($decode_package["dependencies"]["gatsby"]) {
      $this->addStaticPages($routes);
    } else {
      $this->addIndexPage($routes);
    }
    $routes->addRequirements(['_access' => 'TRUE']);

    return $routes;
  }

  /**
   * Public.
   */
  public  function getNameFromPath($path) {
    $base = $this->appRoot . "/data-catalog-frontend/public/";
    $sub = str_replace($base, "", $path);
    return str_replace("/", "__", $sub);
  }

  /**
   * Private.
   */
  private function expandDirectories($base_dir) {
    $directories = [];

    if (!file_exists($base_dir)) {
      return $directories;
    }

    foreach (scandir($base_dir) as $file) {
      if ($file == '.' || $file == '..') {
        continue;
      }
      $dir = $base_dir . DIRECTORY_SEPARATOR . $file;
      if (is_dir($dir)) {
        $directories[] = $dir;
        $directories = array_merge($directories, $this->expandDirectories($dir));
      }
    }
    return $directories;
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
            '_controller' => '\Drupal\frontend\Controller\Page::page',
            'name' => $name,
          ]
      );
    $route->setMethods(['GET']);
    return $route;
  }

  /**
   * Private.
   */
  private function addStaticPages(RouteCollection $routes) {
    $base = $this->appRoot . "/frontend/public";
    $possible_pages = $this->expandDirectories($base);
    foreach ($possible_pages as $possible_page) {
      if (file_exists($possible_page . "/index.html")) {
        $name = self::getNameFromPath($possible_page);
        $path = str_replace($base, "", $possible_page);
        $routes->add($name, $this->routeHelper($path, $name));
      }
    }

    $route = new Route(
      "/home",
      [
        '_controller' => '\Drupal\frontend\Controller\Page::page',
        'name' => 'home',
      ]
    );
    $route->setMethods(['GET']);
    $routes->add('home', $route);
  }

  private function addIndexPage(RouteCollection $routes) {
    $config = \Drupal::config('frontend.routes');
    $config_routes = $config->get('routes');
    foreach ($config_routes as $config_route) {
      $possible_page = explode(",", $config_route);
      $routes->add($possible_page[0], $this->routeHelper($possible_page[1], "home"));
    }
  }
}
