<?php

namespace Drupal\metastore\Plugin\DkanApiDocs;

use Drupal\common\Plugin\DkanApiDocsBase;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\metastore\Service;
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
   * The DKAN metastore service.
   *
   * @var Drupal\metastore\Service
   */
  private $metastore;

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
    $spec = $this->getDoc('metastore');
    foreach ($schemaIds as $schemaId) {
      $spec["components"]["schemas"] += $this->schemaComponent($schemaId);
      $spec["components"]["parameters"]["schemaId"]["examples"]["$schemaId"] = ['value' => $schemaId];
      $spec["components"]["parameters"] += $this->schemaParameters($schemaId);
      $spec['paths'] += $this->schemaPaths($schemaId);
    }
    return $spec;
  }

  private function filterSchemaIds($schemaId) {
    if (in_array($schemaId, ["legacy", "catalog"])) {
      return FALSE;
    }
    if (substr($schemaId, -3) == ".ui") {
      return FALSE;
    }
    return TRUE;
  }

  private function makeIdentifierOptional($schema, $identifierProperty = "identifier") {
    $required = $schema["required"];
    $key = array_search($identifierProperty, $required);
    if (!empty($required) && ($key !== FALSE)) {
      unset($required[$key]);
      $schema["required"] = array_values($required);
    }
    return $schema;
  }

  private function makeAllOptional($schema) {
    $filteredSchema = self::nestedFilterKeys($schema, function ($prop) {
      if ($prop === 'required') {
        return FALSE;
      }
      return TRUE;
    });

    return $filteredSchema;
  }

  private function schemaComponent($schemaId) {
    $schema = json_decode(json_encode($this->metastore->getSchema($schemaId)), TRUE);
    $doc = [
      "{$schemaId}" => self::filterJsonSchemaUnsupported($schema),
    ];

    return $doc;
  }

  private function schemaParameters($schemaId) {
    $doc = [
      "{$schemaId}Uuid" => [
        "name" => "identifier",
        "in" => "path",
        "description" => t("A :schemaId identifier", [":schemaId" => $schemaId]),
        "required" => true,
        "schema" => ["type" => "string"],
        "example" => $this->getExampleIdentifier($schemaId) ?: "00000000-0000-0000-0000-000000000000",
      ],
    ];

    return $doc;
  }

  private function getExampleIdentifier($schemaId) {
    if ($first = $this->metastore->getAll($schemaId)) {
      return $first[0]->{"$.identifier"};
    }
    return FALSE;
  }

  private function schemaPaths($schemaId) {
    $schema = json_decode(json_encode($this->metastore->getSchema($schemaId)), TRUE);
    $tSchema = [':schemaId' => $schemaId];
    $doc = [];

    $doc["/api/1/metastore/schemas/$schemaId/items"] = [

      "post" => [
        "operationId" => "$schemaId-post",
        "summary" => $this->t("Create a new :schemaId.", $tSchema),
        "tags" => [$this->t("Metastore: create")],
        "security" => [
          ['basicAuth' => []],
        ],
        "requestBody" => [
          "required" => TRUE,
          "description" => $this->t("Takes the standard :schemaId schema, but does not require identifier.", $tSchema),
          "content" => [
            "application/json" => [
              "schema" => self::filterJsonSchemaUnsupported($this->makeIdentifierOptional($schema)),
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

    $doc["/api/1/metastore/schemas/$schemaId/items/{identifier}"] = [
      "get" => [
        "operationId" => "$schemaId-get-item",
        "summary" => $this->t("Get a single :schemaId.", $tSchema),
        "tags" => [$this->t("Metastore: get")],
        "parameters" => [
          ['$ref' => "#/components/parameters/{$schemaId}Uuid"],
          ['$ref' => "#/components/parameters/showReferenceIds"],
        ],
        "responses" => [
          "200" => [
            "description" => $this->t("Full :schemaId item.", $tSchema),
            "content" => [
              "application/json" => [
                "schema" => ['$ref' => "#/components/schemas/$schemaId"],
              ],
            ],
          ],
        ],
      ],

      "put" => [
        "operationId" => "$schemaId-put",
        "summary" => $this->t("Fully replace an existing :schemaId", $tSchema),
        "tags" => [$this->t("Metastore: replace")],
        "security" => [
          ['basicAuth' => []],
        ],
        "parameters" => [['$ref' => "#/components/parameters/{$schemaId}Uuid"]],
        "requestBody" => [
          "required" => TRUE,
          "content" => [
            "application/json" => [
              "schema" => ['$ref' => "#/components/schemas/$schemaId"],
            ],
          ],
        ],
        "responses" => [
          "200" => [
            "description" => "Ok.",
          ],
        ],
      ],

      "patch" => [
        "operationId" => "$schemaId-patch",
        "summary" => $this->t("Modify an existing :schemaId", $tSchema),
        "description" => $this->t("Values provided will replace existing values, but required values may be omitted."),
        "tags" => ["Metastore: patch"],
        "security" => [
          ['basicAuth' => []],
        ],
        "parameters" => [['$ref' => "#/components/parameters/{$schemaId}Uuid"]],
        "requestBody" => [
          "required" => TRUE,
          "content" => [
            "application/json" => [
              "schema" => self::filterJsonSchemaUnsupported($this->makeAllOptional($schema)),
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
