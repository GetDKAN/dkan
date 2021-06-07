<?php

namespace Drupal\datastore\Plugin\DkanApiDocs;

use Drupal\common\Plugin\DkanApiDocsBase;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\metastore\Service;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Docs plugin.
 *
 * @DkanApiDocs(
 *  id = "datastore_api_docs",
 *  description = "Datastore docs"
 * )
 */
class DatastoreApiDocs extends DkanApiDocsBase {

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
    $spec = $this->getDoc('datastore');
    $querySchema = self::filterJsonSchemaUnsupported($this->replaceRefs($this->getDoc('datastore', 'query')));

    // Reformat definitions.
    foreach ($querySchema["definitions"] as $key => $def) {
      $schemaName = "datastoreQuery" . ucfirst($key);
      $def["title"] = "Datastore Query: " . (isset($definition["title"]) ? $def["title"] : $key);
      $spec["components"]["schemas"][$schemaName] = $def;
    }
    unset($querySchema["definitions"]);
    $spec["components"]["schemas"]["datastoreQuery"] = $querySchema;

    // Requirements are slightly different if resource is present in path.
    $resourceQuerySchema = $this->resourceQueryAlter($querySchema);
    $spec["components"]["schemas"]["datastoreResourceQuery"] = $resourceQuerySchema;

    // Fill in examples.
    $exampleId = $this->getExampleIdentifier();
    $spec["components"]["parameters"]["datastoreDistributionUuid"]["example"] = $exampleId;
    $spec["paths"]["/api/1/datastore/sql"]["get"]["parameters"][0]["example"] = $this->sqlQueryExample($exampleId);

    // Convert json schema to params.
    $spec = $this->setUpGetParameters($spec);

    return $spec;
  }

  private function setUpGetParameters($spec) {
    foreach ($spec["components"]["schemas"]["datastoreQuery"]["properties"] as $key => $property) {
      $propertyKey = 'datastoreQuery' . ucfirst($key);
      $spec["components"]["parameters"][$propertyKey] = [
        "name" => $key,
        "in" => "query",
        "style" => "deepObject",
        "explode" => TRUE,
        "schema" => [
          '$ref' => "#/components/schemas/datastoreQuery/properties/$key",
        ],
      ];
      $ref = ['$ref' => "#/components/parameters/$propertyKey"];
      $spec["paths"]["/api/1/datastore/query"]["get"]["parameters"][] = $ref;
    }
    foreach ($spec["components"]["schemas"]["datastoreResourceQuery"]["properties"] as $key => $property) {
      $propertyKey = 'datastoreQuery' . ucfirst($key);
      $ref = ['$ref' => "#/components/parameters/$propertyKey"];
      $spec["paths"]["/api/1/datastore/query/{identifier}"]["get"]["parameters"][] = $ref;
    }
    return $spec;
  }

  private function replaceRefs($schema) {
    $newSchema = $schema;
    array_walk_recursive($newSchema, function (&$value, $key) {
      if ($key === '$ref') {
        $parts = explode("/", $value);
        $value = "#/components/schemas/datastoreQuery" . ucfirst($parts[2]);
      }
    });
    return $newSchema;
  }

  private function resourceQueryAlter($schema) {
    unset($schema["properties"]["resources"]);
    unset($schema["properties"]["joins"]);
    $schema["title"] = $this->t("Datastore Resource Query");
    $schema["description"] .= ". When querying against a specific resource, the \"resource\" property is always optional. If you want to set it explicitly, note that it will be aliased to simply \"t\".";
    return $schema;
  }

  private function sqlQueryExample($exampleId) {
    return "[SELECT * FROM $exampleId][LIMIT 2]";
  }

  private function getExampleIdentifier() {
    $all = $this->metastore->getAll("dataset");
    $i = 0;
    $datastore = FALSE;
    $identifier = NULL;
    while ($datastore == FALSE && $i < count($all)) {
      $item = $all[$i];
      $i++;
      if (!isset($item->{'$.distribution[0]'})) {
        continue;
      }
      if (!isset($item->{'$[distribution][0]["%Ref:downloadURL"][0][identifier]'})) {
        continue;
      }
      $identifier = $item->{'$["%Ref:distribution"][0][identifier]'};
      if (!$identifier) {
        continue;
      }
      $datastore = TRUE;
    }
    return $identifier ?: "00000000-0000-0000-0000-000000000000";
  }

}
