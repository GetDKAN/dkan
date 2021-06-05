<?php

namespace Drupal\metastore\Plugin\DkanApiDocs;

use Drupal\common\Plugin\DkanApiDocsBase;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\metastore\Service;
use RootedData\RootedJsonData;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Docs plugin.
 *
 * @DkanApiDocs(
 *  id = "metastore_api_docs",
 *  description = "Metastore docs"
 * )
 */
class MetastoreApiDocs extends DkanApiDocsBase {

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
   *   The module handler service.
   * @param \Drupal\metastore\Service $metastore
   *   The module handler service.
   */
  public function __construct(
    array $configuration,
    $pluginId,
    $pluginDefinition,
    ModuleHandlerInterface $moduleHandler,
    TranslationInterface $stringTranslation,
    Service $metastore
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition, $moduleHandler, $stringTranslation);
    $this->metastore = $metastore;
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
      $container->get('string_translation'),
      $container->get('dkan.metastore.service')
    );
  }

  public function spec() {
    $schemas = $this->metastore->getSchemas();
    $schemaIds = array_filter(array_keys($schemas), [$this, 'filterSchemaIds']);
    $spec = json_decode(file_get_contents($this->docsPath('metastore')), TRUE);
    foreach ($schemaIds as $schemaId) {
      $spec["components"]["parameters"]["schemaId"]["examples"]["$schemaId"] = ['value' => $schemaId];
      $spec['paths'] += $this->schemaDocs($schemaId);
    }
    return $spec;
  }

  private function filterSchemaIds($schemaId) {
    if (in_array($schemaId, ["legacy", "catalog", "distribution"])) {
      return FALSE;
    }
    if (substr($schemaId, -3) == ".ui") {
      return FALSE;
    }
    return TRUE;
  }

  private function makeIdentifierOptional($schema, $identifierProperty = "identifier") {
    $schemaObject = new RootedJsonData(json_encode($schema));
    $required = $schemaObject->{'$.required'};
    $key = array_search($identifierProperty, $required);
    if (!empty($required) && ($key !== FALSE)) {
      unset($required[$key]);
      $required = array_values($required);
      $schemaObject->{'$.required'} = $required;
      $note = $this->t("Required except when creating a new item.");
      $schemaObject->{"$.properties.$identifierProperty.description"} .= " $note";
      $schema = $schemaObject->{'$'};
    }
    return $schema;
  }

  private function filterJsonSchemaUnsupported($schema) {
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

  private static function nestedFilterKeys(array $array, callable $callable) {
    $array = array_filter($array, $callable, ARRAY_FILTER_USE_KEY);
    foreach ($array as &$element) {
      if (is_array($element)) {
        $element = static::nestedFilterKeys($element, $callable);
      }
    }
    return $array;
  }

  private function schemaDocs($schemaId) {
    $schema = json_decode(json_encode($this->metastore->getSchema($schemaId)), TRUE);
    $this->filterJsonSchemaUnsupported($schema);
    $doc = [];

    $doc["/api/1/metastore/schemas/$schemaId/items"] = [

      "post" => [
        "operationId" => "$schemaId-post",
        "summary" => "Create a new $schemaId.",
        "tags" => ["Metastore: create"],
        "security" => [
          ['basicAuth' => []],
        ],
        "requestBody" => [
          "required" => TRUE,
          "content" => [
            "application/json" => [
              "schema" => $this->filterJsonSchemaUnsupported($this->makeIdentifierOptional($schema)),
            ],
          ],
        ],
        "responses" => [
          "200" => [
            "description" => "Ok.",
          ],
        ],
      ],
    ];

    return $doc;
  }

}
