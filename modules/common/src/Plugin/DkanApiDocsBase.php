<?php

namespace Drupal\common\Plugin;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for API Docs plugins.
 *
 * @see \Drupal\plugin_type_example\Annotation\Sandwich
 * @see \Drupal\plugin_type_example\SandwichInterface
 */
abstract class DkanApiDocsBase extends PluginBase implements DkanApiDocsInterface {

  use StringTranslationTrait;

  /**
   * The module handler service.
   *
   * @var mixed
   */
  protected $moduleHandler;

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
   */
  public function __construct(
    array $configuration,
    $pluginId,
    $pluginDefinition,
    ModuleHandlerInterface $moduleHandler,
    TranslationInterface $stringTranslation
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->moduleHandler = $moduleHandler;
    $this->stringTranslation = $stringTranslation;
  }

  /**
   * Container injection.
   *
   * @param \Drupal\common\Plugin\ContainerInterface&ContainerInterface $container
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
      $container->get('module_handler'),
      $container->get('string_translation')
    );
  }

  /**
   * Retrieve the @description property from the annotation and return it.
   *
   * @return string
   */
  public function description() {
    return $this->pluginDefinition['description'];
  }

  protected function docsPath($module = NULL) {
    if ($module) {
      return $this->moduleHandler->getModule($module)->getPath() . '/docs/openapi_spec.json';
    }
    return FALSE;
  }

}
