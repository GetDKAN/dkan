<?php

declare(strict_types=1);

namespace Drupal\json_form_widget\OptionSource;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\json_form_widget\Annotation\JsonFormOptionSource;

/**
 * JsonFormOptionSource plugin manager.
 */
class JsonFormOptionSourcePluginManager extends DefaultPluginManager {

  /**
   * Constructs the object.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/JsonFormOptionSource', $namespaces, $module_handler, JsonFormOptionSourceInterface::class, JsonFormOptionSource::class);
    $this->alterInfo('json_form_option_source_info');
    $this->setCacheBackend($cache_backend, 'json_form_option_source_plugins');
  }

}
