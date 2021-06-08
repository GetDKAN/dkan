<?php

namespace Drupal\common\Plugin;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Routing\RouteProvider;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class DkanApiDocsGenerator implements ContainerInjectionInterface {

  public static function create(ContainerInterface $container) {
    return new DkanApiDocsGenerator(
      $container->get('plugin.manager.dkan_api_docs')
    );
  }

  public function __construct(DkanApiDocsPluginManager $dkanApiDocsPluginManager) {
    $this->docManager = $dkanApiDocsPluginManager;
  }

  public function buildSpec() {
    $docPluginDefinitions = $this->docManager->getDefinitions();
    $spec = [];
    foreach ($docPluginDefinitions as $definition) {
      $pluginSpec = $this->docManager->createInstance($definition['id'])->spec();
      $spec = array_merge_recursive($spec, $pluginSpec);
    }

    $router = \Drupal::service('router.no_access_checks');
    $path = 'api/1/metastore/schemas/{schema_id}/items';
    $collection = \Drupal::service('router.route_provider')->getRoutesByPattern($path);
    $all = $collection->all();
    foreach (array_keys($spec["paths"]) as $path) {
      $route = $router->match($path);
      if (!isset($route['_route_object'])) {
        continue;
      }
      if ($auth = $route['_route_object']->getOption("auth")) {
        
      }
    }

    return $spec;
  }
}
