<?php

namespace Drupal\datastore\Plugin\DkanApiDocs;

use Drupal\common\Plugin\DkanApiDocsBase;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\datastore\Service\Info\ImportInfo;
use Drupal\metastore\MetastoreService;
use RootedData\RootedJsonData;
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
   * @param \Drupal\metastore\MetastoreService $metastore
   *   The module handler service.
   * @param Drupal\datastore\Service\Info\ImportInfo $importInfo
   *   Import info datastoer service.
   */
  public function __construct(
    array $configuration,
    $pluginId,
    $pluginDefinition,
    ModuleHandlerInterface $moduleHandler,
    TranslationInterface $stringTranslation,
    MetastoreService $metastore,
    ImportInfo $importInfo
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition, $moduleHandler, $stringTranslation);
    $this->metastore = $metastore;
    $this->importInfo = $importInfo;
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
      $container->get('dkan.metastore.service'),
      $container->get('dkan.datastore.import_info')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function spec() {
    $spec = $this->getDoc('datastore');
    $querySchema = self::filterJsonSchemaUnsupported($this->replaceRefs($this->getDoc('datastore', 'query')));

    // Reformat definitions.
    foreach ($querySchema["definitions"] as $key => $def) {
      $schemaName = "datastoreQuery" . ucfirst($key);
      $def["title"] = "Datastore Query: " . (isset($def["title"]) ? $def["title"] : $key);
      $spec["components"]["schemas"][$schemaName] = $def;
    }
    unset($querySchema["definitions"]);
    $spec["components"]["schemas"]["datastoreQuery"] = $querySchema;

    // Requirements are slightly different if resource is present in path.
    $resourceQuerySchema = $this->resourceQueryAlter($querySchema);
    $spec["components"]["schemas"]["datastoreResourceQuery"] = $resourceQuerySchema;

    // Fill in examples.
    $spec = $this->setUpExamples($spec);
    // Convert json schema to params.
    $spec = $this->setUpGetParameters($spec);

    return $spec;
  }

  /**
   * Set up examples throughout spec from real data.
   *
   * @param array $spec
   *   Openapi spec.
   *
   * @return array
   *   Modified spec.
   */
  private function setUpExamples(array $spec) {
    $exampleIds = $this->getExampleIdentifiers();
    $spec["components"]["parameters"]["datastoreDistributionUuid"]["example"] = $exampleIds['distribution'];
    $spec["components"]["parameters"]["datastoreDatasetUuid"]["example"] = $exampleIds['dataset'];
    $spec["components"]["parameters"]["datastoreDistributionIndex"]["example"] = $exampleIds['datasetDistributionIndex'];
    $spec["paths"]["/api/1/datastore/query"]["post"]["requestBody"]["content"]["application/json"]["example"]
      = $this->queryExample($exampleIds['distribution']);
    $spec["paths"]["/api/1/datastore/query/download"]["post"]["requestBody"]["content"]["application/json"]["example"]
      = $this->queryExample($exampleIds['distribution'], "csv");
    $spec["paths"]["/api/1/datastore/query/{distributionId}"]["post"]["requestBody"]["content"]["application/json"]["example"]
      = $this->queryExample();
    $spec["paths"]["/api/1/datastore/query/{datasetId}/{index}"]["post"]["requestBody"]["content"]["application/json"]["example"]
      = $this->queryExample();

    $spec['components']['parameters']['datastoreUuid']["example"] = $exampleIds['resource'];

    $spec["paths"]["/api/1/datastore/sql"]["get"]["parameters"][0]["example"] = $this->sqlQueryExample($exampleIds['distribution']);
    return $spec;
  }

  /**
   * Set up the GET parameters for datastore queries. WIP.
   *
   * @param array $spec
   *   OpenApi spec.
   *
   * @return array
   *   Modified spec.
   */
  private function setUpGetParameters(array $spec) {
    $flatQueryProperties = array_filter(
      $spec["components"]["schemas"]["datastoreQuery"]["properties"],
      [$this, 'propertyIsFlat']
    );
    foreach (array_keys($flatQueryProperties) as $key) {
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
      $spec["paths"]["/api/1/datastore/query/download"]["get"]["parameters"][] = $ref;
      $spec["paths"]["/api/1/datastore/query/{distributionId}"]["get"]["parameters"][] = $ref;
      $spec["paths"]["/api/1/datastore/query/{datasetId}/{index}"]["get"]["parameters"][] = $ref;
    }

    return $spec;
  }

  /**
   * Check to see if a property can be used for GET params.
   *
   * @param array $property
   *   Property definition from spec.
   *
   * @return bool
   *   False if array or object property.
   */
  private function propertyIsFlat(array $property): bool {
    if (in_array($property['type'], ['array', 'object'])) {
      return FALSE;
    }
    return TRUE;
  }

  /**
   * Fix schema references to reflect name normalization.
   *
   * @param array $schema
   *   OpenApi datastore schema.
   *
   * @return array
   *   Modified schema.
   */
  private function replaceRefs(array $schema) {
    $newSchema = $schema;
    array_walk_recursive($newSchema, function (&$value, $key) {
      if ($key === '$ref') {
        $parts = explode("/", $value);
        $value = "#/components/schemas/datastoreQuery" . ucfirst($parts[2]);
      }
    });
    return $newSchema;
  }

  /**
   * Modify the query api schema for resource-specific endpoint.
   *
   * @param array $schema
   *   The datastore query schema.
   *
   * @return array
   *   Modified schema.
   */
  private function resourceQueryAlter(array $schema) {
    unset($schema["properties"]["resources"]);
    unset($schema["properties"]["joins"]);
    $schema["title"] = $this->t("Datastore Resource Query");
    $schema["description"] .= ". When querying against a specific resource, the \"resource\" property is always optional. If you want to set it explicitly, note that it will be aliased to simply \"t\".";
    return $schema;
  }

  /**
   * Generate an example SQL query string.
   *
   * @param string $exampleId
   *   Example distirbution/resource identifier.
   *
   * @return string
   *   SQL query string.
   */
  private function sqlQueryExample(string $exampleId) {
    return "[SELECT * FROM $exampleId][LIMIT 2]";
  }

  /**
   * Generate an example query array for an ID.
   *
   * @param string|null $exampleId
   *   The datastore resource identifier.
   * @param string|null $format
   *   Format property for the query.
   *
   * @return array
   *   Query array.
   */
  private function queryExample($exampleId = NULL, $format = NULL) {
    $query = [
      'conditions' => [
        [
          'resource' => 't',
          'property' => 'record_number',
          'value' => 1,
          'operator' => '>',
        ],
      ],
      'limit' => 3,
    ];
    if (isset($exampleId)) {
      $query['resources'] = [
        ['id' => $exampleId, 'alias' => 't'],
      ];
    }
    if (isset($format)) {
      $query['format'] = $format;
    }
    return $query;
  }

  /**
   * Get some example identifiers to populate docs.
   *
   * @return array
   *   An array, with keys resource and distribution.
   *
   * @todo Page through results in case 20 isn't enough.
   */
  private function getExampleIdentifiers() {
    $all = $this->metastore->getAll("dataset", 0, 20);
    $i = 0;
    $datastore = FALSE;
    $identifiers = [];
    while ($datastore == FALSE && $i < count($all)) {
      $item = $all[$i];
      $i++;
      $datastore = (bool) ($identifiers = $this->getDatastoreIds($item));
    }
    return array_merge(
      [
        'resource' => '00000000000000000000000000000000__0000000000__source',
        'distribution' => "00000000-0000-0000-0000-000000000000",
        'dataset' => "00000000-0000-0000-0000-000000000000",
        'datasetDistributionIndex' => 0,
      ],
      $identifiers ?: []
    );
  }

  /**
   * Get the datastore identifiers for a dataset.
   *
   * @param \RootedData\RootedJsonData $dataset
   *   Dataset JSON data object.
   *
   * @return false|array
   *   FALSE if none found, resource/datastore/dataset array if found.
   */
  private function getDatastoreIds(RootedJsonData $dataset) {
    if (!isset($dataset->{'$.distribution[0]'})) {
      return FALSE;
    }
    foreach ($dataset->{'$[\'%Ref:distribution\']'} as $index => $distribution) {
      if ($identifiers = $this->getIdentifiersFromDistribution($distribution)) {
        $identifiers['dataset'] = $dataset->{'$.identifier'};
        $identifiers['datasetDistributionIndex'] = (string) $index;
        break;
      }
    }
    return $identifiers;
  }

  /**
   * Get the identifiers array from a distribution array.
   *
   * @param array $distribution
   *   A distribution extracted from a dataset item.
   *
   * @return false|array
   *   An array of distribution and datastore IDs, or false.
   */
  private function getIdentifiersFromDistribution(array $distribution) {
    if (!($resourceId = $distribution["data"]["%Ref:downloadURL"][0]["identifier"])) {
      return FALSE;
    }
    if (!$this->resourceHasDatastore($resourceId)) {
      return FALSE;
    }
    return [
      'distribution' => $distribution["identifier"],
      'datastore' => $resourceId,
    ];

  }

  /**
   * Make 3-part resource ID into 2-part.
   *
   * @param string $resourceId
   *   A three-part resource ID.
   *
   * @return string
   *   A two-part resource ID.
   */
  private function removePerspective($resourceId) {
    $parts = explode("__", $resourceId);
    if (count($parts) != 3) {
      return $resourceId;
    }
    unset($parts[2]);
    return implode("_", $parts);
  }

  /**
   * Determine if this resource has been imported to the datastore.
   *
   * @param string $resourceId
   *   A resource ID.
   *
   * @return bool
   *   TRUE if imported.
   */
  private function resourceHasDatastore($resourceId) {
    $resourceId = $this->removePerspective($resourceId);
    $parts = explode("_", $resourceId);
    try {
      $import = $this->importInfo->getItem($parts[0], $parts[1]);
    }
    catch (\Exception $e) {
      return FALSE;
    }
    if (isset($import->importerPercentDone) && ($import->importerPercentDone == 100)) {
      return TRUE;
    }
    return FALSE;
  }

}
