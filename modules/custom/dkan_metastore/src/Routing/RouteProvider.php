<?php

namespace Drupal\dkan_metastore\Routing;

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Class.
 */
class RouteProvider {

  private $configFactory;

  /**
   * Constructor.
   */
  public function __construct(ConfigFactoryInterface $configFactory) {
    $this->configFactory = $configFactory;
  }

  /**
   * Get property list.
   *
   * @return array
   *   list of json properties being considered from DKAN json property api
   *   config value.
   *
   * @Todo: consolidate with dkan_data ValueReferencer's getPropertyList.
   */
  public function getPropertyList() {
    $list = $this->configFactory->get('dkan_data.settings')->get('property_list');
    return array_values(array_filter($list));
  }

  /**
   * Inherited.
   *
   * {@inheritdoc}
   */
  public function routes() {
    $routes = new RouteCollection();
    $public_routes = new RouteCollection();
    $authenticated_routes = new RouteCollection();

    $public_routes->addCollection($this->datasetPublicRoutes());
    $public_routes->addCollection($this->propertyPublicRoutes());
    $this->setPublicRoutesAccess($public_routes);
    $routes->addCollection($public_routes);

    $authenticated_routes->addCollection($this->datasetAuthenticatedRoutes());
    $authenticated_routes->addCollection($this->propertyAuthenticatedRoutes());
    $this->setPrivateRoutesAccess($authenticated_routes);
    $routes->addCollection($authenticated_routes);

    return $routes;
  }

  /**
   * Private.
   */
  private function datasetPublicRoutes() {
    $datasetPublic = new RouteCollection();
    $datasetPublic->add("dkan_api.dataset.get_all", $this->routeHelper(
      'dataset', 'dataset', 'GET', 'getAll')
    );
    $datasetPublic->add("dkan_api.dataset.get", $this->routeHelper(
      'dataset', 'dataset/{uuid}', 'GET', 'get')
    );
    $datasetPublic->add("dkan_api.dataset.get_resources", $this->routeHelper(
      'dataset', 'dataset/{uuid}/resources', 'GET', 'getResources')
    );
    return $datasetPublic;
  }

  /**
   * Private.
   */
  private function datasetAuthenticatedRoutes() {
    $datasetAuthenticated = new RouteCollection();
    $datasetAuthenticated->add("dkan_api.dataset.post", $this->routeHelper(
      'dataset', 'dataset', 'POST', 'post')
    );
    $datasetAuthenticated->add("dkan_api.dataset.put", $this->routeHelper(
      'dataset', 'dataset/{uuid}', 'PUT', 'put')
    );
    $datasetAuthenticated->add("dkan_api.dataset.patch", $this->routeHelper(
      'dataset', 'dataset/{uuid}', 'PATCH', 'patch')
    );
    $datasetAuthenticated->add("dkan_api.dataset.delete", $this->routeHelper(
      'dataset', 'dataset/{uuid}', 'DELETE', 'delete')
    );
    return $datasetAuthenticated;
  }

  /**
   * Private.
   */
  private function propertyPublicRoutes() {
    $propertyPublic = new RouteCollection();
    foreach ($this->getPropertyList() as $schema) {
      $propertyPublic->add("dkan_api.{$schema}.get_all", $this->routeHelper(
        $schema, $schema, 'GET', 'getAll')
      );
      $propertyPublic->add("dkan_api.{$schema}.get", $this->routeHelper(
        $schema, "{$schema}/{uuid}", 'GET', 'get')
      );
    }
    return $propertyPublic;
  }

  /**
   * Private.
   */
  private function propertyAuthenticatedRoutes() {
    $propertyAuthenticated = new RouteCollection();
    foreach ($this->getPropertyList() as $schema) {
      $propertyAuthenticated->add("dkan_metastore.t{$schema}.put", $this->routeHelper(
        $schema, "{$schema}/{uuid}", 'PUT', 'put')
      );
      $propertyAuthenticated->add("dkan_metastore.{$schema}.patch", $this->routeHelper(
        $schema, "{$schema}/{uuid}", 'PATCH', 'patch')
      );
    }
    return $propertyAuthenticated;
  }

  /**
   * Private.
   */
  private function setPublicRoutesAccess($routes) {
    $routes->addRequirements(['_access' => 'TRUE']);
  }

  /**
   * Private.
   */
  private function setPrivateRoutesAccess($routes) {
    $routes->addRequirements(['_permission' => 'post put delete datasets through the api']);
    $routes->addOptions(['_auth' => ['basic_auth']]);
  }

  /**
   * Private.
   */
  private function routeHelper(string $schema, string $path, string $httpVerb, string $datasetMethod) : Route {
    $route = new Route(
      "/api/v1/{$path}",
          [
            '_controller' => '\Drupal\dkan_metastore\Controller\Api::' . $datasetMethod,
            'schema_id' => $schema,
          ]
      );
    $route->setMethods([$httpVerb]);
    return $route;
  }

}
