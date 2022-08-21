<?php

namespace Drupal\dkan\Schema;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\PluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class MetastoreSchema extends PluginBase implements MetastoreSchemaInterface {
  
  /**
   * Constructs a \Drupal\Component\Plugin\PluginBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $pluginId
   *   The plugin_id for the plugin instance.
   * @param mixed $pluginDefinition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler service.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $stringTranslation
   *   String translation service.
   */
  public function __construct(
    array $configuration,
    $pluginId,
    $pluginDefinition,
    ModuleHandlerInterface $moduleHandler
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->moduleHandler = $moduleHandler;
  }

    /**
   * Container injection.
   *
   * @param \Drupal\common\Plugin\ContainerInterface $container
   *   The service container.
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $pluginId
   *   The plugin_id for the plugin instance.
   * @param mixed $pluginDefinition
   *   The plugin implementation definition.
   *
   * @return static
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $pluginId,
    $pluginDefinition
  ) {
    return new static(
      $configuration,
      $pluginId,
      $pluginDefinition,
      $container->get('module_handler')
    );
  }

  public function getSchemaId(): string {
    return $this->getPluginId();
  }

  public function getSchema(): object {
    $schema_path = $this->pluginDefinition['schema_path'];
    $module_path = $this->moduleHandler
      ->getModule($this->pluginDefinition['provider'])
      ->getPath();

    $schema_str = file_get_contents("$module_path/$schema_path");
    return json_decode($schema_str);
  }

  public function getUiSchema(): object {
    return (object) [];
  }
}