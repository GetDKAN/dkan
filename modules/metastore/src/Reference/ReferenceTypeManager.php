<?php

namespace Drupal\metastore\Reference;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * A plugin manager for DKAN metastore reference definitions.
 *
 * More docs to come.
 */
class ReferenceTypeManager extends DefaultPluginManager {

  /**
   * Creates the discovery object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    $subdir = 'Plugin/MetastoreReferenceType';
    $plugin_interface = 'Drupal\metastore\Reference\ReferenceTypeInterface';

    // The name of the annotation class that contains the plugin definition.
    $plugin_definition_annotation_name = 'Drupal\metastore\Annotation\MetastoreReferenceType';

    parent::__construct($subdir, $namespaces, $module_handler, $plugin_interface, $plugin_definition_annotation_name);

    $this->alterInfo('metastore_reference_type_info');
    $this->setCacheBackend($cache_backend, 'metastore_reference_type_plugins');
  }

}
