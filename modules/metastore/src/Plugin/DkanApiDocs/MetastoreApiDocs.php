<?php

namespace Drupal\metastore\Plugin\DkanApiDocs;

use Drupal\common\Plugin\DkanApiDocsBase;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\metastore\MetastoreService;
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

  const DOC_SCHEMAS = ['dataset'];

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
   * @param \Drupal\metastore\MetastoreService $metastore
   *   The metastore service.
   */
  public function __construct(
    array $configuration,
    $pluginId,
    $pluginDefinition,
    ModuleHandlerInterface $moduleHandler,
    TranslationInterface $stringTranslation,
    MetastoreService $metastore
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

  /**
   * {@inheritdoc}
   */
  public function spec() {
    $spec = $this->getDoc('metastore');

    $exampleSchemaIds = array_values(array_filter(
      array_keys($this->metastore->getSchemas()),
      [$this, 'filterSchemaIds']
    ));
    foreach ($exampleSchemaIds as $schemaId) {
      $spec["components"]["parameters"]["schemaId"]["examples"]["$schemaId"] = ['value' => $schemaId];
    }

    $schemaIds = self::DOC_SCHEMAS;
    foreach ($schemaIds as $schemaId) {
      $spec["components"]["schemas"] += $this->schemaComponent($schemaId);
      $spec["components"]["parameters"] += $this->schemaParameters($schemaId);
      $spec['paths'] += $this->schemaPaths($schemaId);

      $tSchema = [':schemaId' => $schemaId];
      $spec['tags'][] = [
        'name' => $this->t("Metastore: :schemaId", $tSchema),
        'description' => $this->t(
          'CRUD operations for :schemaId metastore items. Substitute any other schema name for ":schemaId" to modify other items.',
          $tSchema
        ),
      ];
    }

    // Copy one of the UUID parameters to a generic one for
    // non-schema-specific URLs.
    $spec["components"]["parameters"]["exampleUuid"] = $spec["components"]["parameters"]["{$schemaIds[0]}Uuid"];

    return $spec;
  }

  /**
   * Determine whether a schemaID should be used in the docs.
   *
   * @param string $schemaId
   *   A schema ID.
   *
   * @return bool
   *   TRUE if it should be included.
   */
  private function filterSchemaIds($schemaId) {
    if (in_array($schemaId, ["legacy", "catalog"])) {
      return FALSE;
    }
    if (substr($schemaId, -3) == ".ui") {
      return FALSE;
    }
    return TRUE;
  }

  /**
   * Alter the metastore schema to make identifiers optional for posts.
   *
   * @param array $schema
   *   An openapi schema array.
   * @param string $identifierProperty
   *   The property in the schema that holds the required identifier.
   *
   * @return array
   *   Altered schema.
   */
  private function makeIdentifierOptional(array $schema, $identifierProperty = "identifier") {
    $required = $schema["required"];
    $key = array_search($identifierProperty, $required);
    if (!empty($required) && ($key !== FALSE)) {
      unset($required[$key]);
      $schema["required"] = array_values($required);
    }
    return $schema;
  }

  /**
   * Make all properties optional (for patch).
   *
   * @param array $schema
   *   Schema array.
   *
   * @return array
   *   Altered schema.
   */
  private function makeAllOptional(array $schema) {
    $filteredSchema = self::nestedFilterKeys($schema, function ($prop) {
      if ($prop === 'required') {
        return FALSE;
      }
      return TRUE;
    });

    return $filteredSchema;
  }

  /**
   * Add filtered schema to components from schemaId.
   *
   * @param string $schemaId
   *   Schema ID.
   *
   * @return array
   *   An array with one key, the schemaID.
   */
  private function schemaComponent($schemaId) {
    $schema = json_decode(json_encode($this->metastore->getSchema($schemaId)), TRUE);
    $doc = [
      "{$schemaId}" => self::filterJsonSchemaUnsupported($schema),
    ];

    return $doc;
  }

  /**
   * Create parameters with examples for each schema ID.
   *
   * @param string $schemaId
   *   The schema ID (e.g. "dataset).
   *
   * @return array
   *   Array with single key, value is full parameter array.
   */
  private function schemaParameters($schemaId) {
    $doc = [
      "{$schemaId}Uuid" => [
        "name" => "identifier",
        "in" => "path",
        "description" => $this->t("A :schemaId identifier", [":schemaId" => $schemaId]),
        "required" => TRUE,
        "schema" => ["type" => "string"],
        "example" => $this->getExampleIdentifier($schemaId) ?: "00000000-0000-0000-0000-000000000000",
      ],
    ];

    return $doc;
  }

  /**
   * Get a working example identifier for the schema ID.
   *
   * @param string $schemaId
   *   A schema ID.
   *
   * @return string
   *   An identifier.
   */
  private function getExampleIdentifier($schemaId) {
    if ($first = $this->metastore->getAll($schemaId, 0, 1)) {
      return $first[0]->{"$.identifier"};
    }
    return FALSE;
  }

  /**
   * Create set of paths for a specific schema.
   *
   * @param string $schemaId
   *   A schema ID.
   *
   * @return array
   *   An array of paths for openapi.
   */
  private function schemaPaths($schemaId) {
    $schema = json_decode(json_encode($this->metastore->getSchema($schemaId)), TRUE);
    $doc = [];

    $doc["/api/1/metastore/schemas/$schemaId/items"] = [
      "post" => $this->schemaItemPost($schemaId, $schema),
    ];

    $doc["/api/1/metastore/schemas/$schemaId/items/{identifier}"] = [
      "get" => $this->schemaItemGet($schemaId),
      "put" => $this->schemaItemPut($schemaId),
      "patch" => $this->schemaItemPatch($schemaId, $schema),
    ];

    return $doc;
  }

  /**
   * Create the openapi post method for a metastore item.
   *
   * @param string $schemaId
   *   A schema ID, e.g. "dataset".
   * @param array $schema
   *   The full metastore schema.
   *
   * @return array
   *   Full request array to be added to spec.
   */
  private function schemaItemPost($schemaId, array $schema) {
    $tSchema = [':schemaId' => $schemaId];
    return [
      "operationId" => "$schemaId-post",
      "summary" => $this->t("Create a new :schemaId.", $tSchema),
      "tags" => [$this->t("Metastore: :schemaId", $tSchema)],
      "security" => [['basic_auth' => []]],
      "requestBody" => [
        "required" => TRUE,
        "description" => $this->t("Takes the standard :schemaId schema, but does not require identifier.\n\nAutomatic example not yet available; try retrieving a :schemaId via GET, removing the identifier property, and pasting to test.", $tSchema),
        "content" => [
          "application/json" => [
            "schema" => self::filterJsonSchemaUnsupported($this->makeIdentifierOptional($schema)),
          ],
        ],
      ],
      "responses" => [
        "201" => [
          "description" => "Metadata creation successful.",
          "content" => [
            "application/json" => ["schema" => ['$ref' => '#/components/schemas/metastoreWriteResponse']],
          ],
        ],
        '400' => ['$ref' => '#/components/responses/400BadJson'],
      ],
    ];
  }

  /**
   * Create the openapi get method for a metastore item.
   *
   * @param string $schemaId
   *   A schema ID, e.g. "dataset".
   *
   * @return array
   *   Full request array to be added to spec.
   */
  private function schemaItemGet($schemaId) {
    $tSchema = [':schemaId' => $schemaId];
    return [
      "operationId" => "$schemaId-get-item",
      "summary" => $this->t("Get a single :schemaId.", $tSchema),
      "tags" => [$this->t("Metastore: :schemaId", $tSchema)],
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
        "404" => ['$ref' => '#/components/responses/404IdNotFound'],
      ],
    ];
  }

  /**
   * Create the openapi put method for a metastore item.
   *
   * @param string $schemaId
   *   A schema ID, e.g. "dataset".
   *
   * @return array
   *   Full request array to be added to spec.
   */
  private function schemaItemPut($schemaId) {
    $tSchema = [':schemaId' => $schemaId];
    return [
      "operationId" => "$schemaId-put",
      "summary" => $this->t("Replace a :schemaId", $tSchema),
      "description" => $this->t("Object will be completely replaced; optional properties not included in the request will be deleted.\n\nAutomatic example not yet available; try retrieving a :schemaId via GET, changing values, and pasting to test.", $tSchema),
      "tags" => [$this->t("Metastore: :schemaId", $tSchema)],
      "security" => [
        ['basic_auth' => []],
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
        "412" => ['$ref' => '#/components/responses/412MetadataObjectNotFound'],
      ],
    ];
  }

  /**
   * Create the openapi patch method for a metastore item.
   *
   * @param string $schemaId
   *   A schema ID, e.g. "dataset".
   * @param array $schema
   *   The full metastore schema.
   *
   * @return array
   *   Full request array to be added to spec.
   */
  private function schemaItemPatch($schemaId, array $schema) {
    $tSchema = [':schemaId' => $schemaId];
    return [
      "operationId" => "$schemaId-patch",
      "summary" => $this->t("Modify an existing :schemaId", $tSchema),
      "description" => $this->t("Values provided will replace existing values, but required values may be omitted.\n\nAutomatic example not yet available; try retrieving a :schemaId via GET, changing values, removing unchanged properties, and pasting to test.", $tSchema),
      "tags" => [$this->t("Metastore: :schemaId", $tSchema)],
      "security" => [
        ['basic_auth' => []],
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
        "412" => ['$ref' => '#/components/responses/412MetadataObjectNotFound'],
      ],
    ];
  }

}
