<?php

namespace Drupal\dkan\Schema;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\Extension;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Plugin\Discovery\YamlDiscovery;

/**
 * Provides a default plugin manager for link relation types.
 *
 * @see \Drupal\Core\Http\LinkRelationTypeInterface
 */
class SchemaManager extends DefaultPluginManager {

  /**
   * {@inheritdoc}
   */
  protected $defaults = [
    'class' => MetastoreSchema::class,
  ];

  /**
   * The app root.
   *
   * @var string
   */
  protected $root;

  /**
   * Constructs a new SchemaManager.
   *
   * @param string $root
   *   The app root.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   The cache backend.
   */
  public function __construct($root, ModuleHandlerInterface $module_handler, CacheBackendInterface $cache) {
    $this->root = $root;
    $this->pluginInterface = MetastoreSchemaInterface::class;
    $this->moduleHandler = $module_handler;
    $this->setCacheBackend($cache, 'dkan_schema_plugins', ['dkan_schema']);
  }

  /**
   * {@inheritdoc}
   */
  protected function getDiscovery() {
    if (!$this->discovery) {
      $directories = ['core' => $this->root . '/core'];
      $directories += array_map(function (Extension $extension) {
        return $this->root . '/' . $extension->getPath();
      }, $this->moduleHandler->getModuleList());
      $this->discovery = new YamlDiscovery('dkan_schemas', $directories);
    }
    return $this->discovery;
  }

}
