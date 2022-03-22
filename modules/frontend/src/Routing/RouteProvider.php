<?php

namespace Drupal\frontend\Routing;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\Query\QueryFactoryInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * DKAN Frontend route provider.
 */
class RouteProvider {

  /**
   * App root directory for react data catalog app.
   *
   * @var string
   */
  private $appRoot;

  /**
   * Constructor.
   */
  public function __construct(string $appRoot, QueryFactoryInterface $entityQuery, ConfigFactoryInterface $configFactory) {
    $this->appRoot = $appRoot;
    $this->entityQuery = $entityQuery;
    $this->buildFolder = $configFactory->get('frontend.config')->get('build_folder');
    $this->frontendPath = $configFactory->get('frontend.config')->get('frontend_path');
    $this->routes = $configFactory->get('frontend.config')->get('routes');
  }

  /**
   * Routes.
   */
  public function routes() {
    $routes = new RouteCollection();

    $package_json_path = $this->appRoot . $this->frontendPath . "/package.json";
    if (is_file($package_json_path)) {
      $package_json = file_get_contents($package_json_path);
      $decode_package = json_decode($package_json, TRUE);
    }
    if (isset($decode_package["dependencies"]["gatsby"])) {
      $this->addStaticPages($routes);
    }
    else {
      $this->addIndexPage($routes);
    }
    $routes->addRequirements(['_access' => 'TRUE']);

    return $routes;
  }

  /**
   * Public.
   */
  public function getNameFromPath($path) {
    $base = $this->appRoot . $this->frontendPath . $this->buildFolder;
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
   * Private. Each route returns its own JS file.
   */
  private function addStaticPages(RouteCollection $routes) {
    $base = $this->appRoot . $this->frontendPath . $this->buildFolder;
    $possible_pages = $this->expandDirectories($base);
    foreach ($possible_pages as $possible_page) {
      if (file_exists($possible_page . "/index.html")) {
        $name = $this->getNameFromPath($possible_page);
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

  /**
   * Private. All routes return root JS file.
   */
  private function addIndexPage(RouteCollection $routes) {
    $config_routes = $this->routes;
    foreach ($config_routes as $config_route) {
      $possible_page = explode(",", $config_route);
      $routes->add($possible_page[0], $this->routeHelper($possible_page[1], "home"));
    }

  }

}
