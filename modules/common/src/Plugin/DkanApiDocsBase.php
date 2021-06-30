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
   * @param \Drupal\Core\StringTranslation\TranslationInterface $stringTranslation
   *   String translation service.
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
      $container->get('module_handler'),
      $container->get('string_translation')
    );
  }

  /**
   * Retrieve the @description property from the annotation and return it.
   *
   * @return string
   *   Description.
   */
  public function description() {
    return $this->pluginDefinition['description'];
  }

  /**
   * Return a JSON openapi document as a decoded array.
   *
   * @param mixed|null $module
   *   Module machine name.
   * @param string $docName
   *   Filename, without .json extension, to retrieve from modulename/docs.
   *
   * @return array|false
   *   Decoded document.
   */
  protected function getDoc($module = NULL, $docName = "openapi_spec") {
    if ($module) {
      $path = $this->moduleHandler->getModule($module)->getPath() . "/docs/$docName.json";
      return json_decode(file_get_contents($path), TRUE);
    }
    return FALSE;
  }

  /**
   * Remove JSON Schema properties not supported by OpenAPI 3.
   *
   * @param array $schema
   *   A JSON schema, decoded to an associative array.
   *
   * @return array
   *   Filtered schema.
   */
  protected static function filterJsonSchemaUnsupported(array $schema) {
    $filteredSchema = self::nestedFilterKeys($schema, function ($prop) {
      $notSupported = [
        '$schema',
        'additionalItems',
        'const',
        'contains',
        'dependencies',
        'id',
        '$id',
        'patternProperties',
        'propertyNames',
        'enumNames',
        'examples',
      ];

      if (!is_numeric($prop) && in_array($prop, $notSupported)) {
        return FALSE;
      }
      return TRUE;
    });

    return $filteredSchema;
  }

  /**
   * Recursively filter an array by keys.
   *
   * @param array $array
   *   The array to filter.
   * @param callable $callable
   *   Callback function.
   *
   * @return array
   *   The filtered array.
   */
  protected static function nestedFilterKeys(array $array, callable $callable) {
    $array = array_filter($array, $callable, ARRAY_FILTER_USE_KEY);
    foreach ($array as &$element) {
      if (is_array($element)) {
        $element = static::nestedFilterKeys($element, $callable);
      }
    }
    return $array;
  }

}
