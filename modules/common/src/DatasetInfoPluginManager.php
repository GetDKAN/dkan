<?php

declare(strict_types=1);

namespace Drupal\common;

use Drupal\common\Annotation\DatasetInfoPlugin;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * DatasetInfo plugin manager.
 */
final class DatasetInfoPluginManager extends DefaultPluginManager {

  /**
   * Constructs the object.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/DatasetInfo', $namespaces, $module_handler, DatasetInfoPluginInterface::class, DatasetInfoPlugin::class);
    $this->alterInfo('dataset_info_info');
    $this->setCacheBackend($cache_backend, 'dataset_info_plugins');
  }

}
