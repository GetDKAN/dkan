<?php

namespace Drupal\dkan_api\Routing;

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 *
 */
class RouteProvider {

  /**
   * @return array
   *   list of json properties being considered from DKAN json property api
   *   config value.
   *
   * @Todo: consolidate with dkan_data ValueReferencer's getPropertyList.
   */
  public function getPropertyList() {
    $list = \Drupal::config('dkan_data.settings')->get('property_list');
    return array_values(array_filter($list));
  }

  /**
   * {@inheritdoc}
   */
  public function routes() {
    $routes = new RouteCollection();
    $public_routes = new RouteCollection();
    $authenticated_routes = new RouteCollection();
    $schemas = array_merge(['dataset'], $this->getPropertyList());

    foreach ($schemas as $schema) {
      // GET collection.
      $get_all = $this->routeHelper($schema, "/api/v1/$schema", 'GET', 'getAll');
      $public_routes->add("dkan_api.{$schema}.get_all", $get_all);
      // GET individual.
      $get = $this->routeHelper($schema, "/api/v1/$schema/{uuid}", 'GET', 'get');
      $public_routes->add("dkan_api.{$schema}.get", $get);
      // POST.
      $post = $this->routeHelper($schema, "/api/v1/$schema", 'POST', 'post');
      $authenticated_routes->add("dkan_api.{$schema}.post", $post);
      // PUT.
      $put = $this->routeHelper($schema, "/api/v1/$schema/{uuid}", 'PUT', 'put');
      $authenticated_routes->add("dkan_api.{$schema}.put", $put);
      // PATCH.
      $patch = $this->routeHelper($schema, "/api/v1/$schema/{uuid}", 'PATCH', 'patch');
      $authenticated_routes->add("dkan_api.{$schema}.patch", $patch);
      // DELETE.
      $delete = $this->routeHelper($schema, "/api/v1/$schema/{uuid}", 'DELETE', 'delete');
      $authenticated_routes->add("dkan_api.{$schema}.delete", $delete);
    }

    $public_routes->addRequirements(['_access' => 'TRUE']);
    $authenticated_routes->addRequirements(['_permission' => 'post put delete datasets through the api']);
    $authenticated_routes->addOptions(['_auth' => ['basic_auth']]);
    $routes->addCollection($public_routes);
    $routes->addCollection($authenticated_routes);

    return $routes;
  }

  /**
   * @param string $path
   * @param string $datasetMethod
   * @param string $httpVerb
   * @return Route
   */
  protected function routeHelper(string $schema, string $path, string $httpVerb, string $datasetMethod) : Route {
    $route = new Route(
      $path,
      [
        '_controller' => '\Drupal\dkan_api\Controller\Dataset::' . $datasetMethod,
        'schema_id' => $schema,
      ]
    );
    $route->setMethods([$httpVerb]);
    return $route;
  }

}
