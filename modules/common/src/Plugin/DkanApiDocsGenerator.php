<?php

namespace Drupal\common\Plugin;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
    $items = [];
    foreach ($docPluginDefinitions as $definition) {
      $items[] = $definition['class']::spec();
    }
    return $items;
  }
}
