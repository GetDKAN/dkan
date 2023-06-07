<?php

namespace Drupal\datastore\Controller;

use Drupal\common\DatasetInfo;
use Drupal\datastore\Service\DatastoreQuery;
use Drupal\datastore\Service\Query as QueryService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\common\JsonResponseTrait;
use RootedData\RootedJsonData;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\metastore\MetastoreApiResponse;
use JsonSchema\Validator;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

/**
 * Abstract Controller providing base functionality used to query datastores.
 *
 * @package Drupal\datastore
 */
abstract class AbstractQueryController implements ContainerInjectionInterface {
  use JsonResponseTrait;

  /**
   * Datastore query service.
   *
   * @var \Drupal\datastore\Service\Query
   */
  protected QueryService $queryService;

  /**
   * DatasetInfo Service.
   *
   * @var \Drupal\common\DatasetInfo
   */
  protected DatasetInfo $datasetInfo;

  /**
   * ConfigFactory object.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected ConfigFactoryInterface $configFactory;

  /**
   * Metastore API response.
   *
   * @var \Drupal\metastore\MetastoreApiResponse
   */
  protected MetastoreApiResponse $metastoreApiResponse;

  /**
   * Default API rows limit.
   *
   * @var int
   */
  protected const DEFAULT_ROWS_LIMIT = 500;

  /**
   * Api constructor.
   */
  public function __construct(
    QueryService $queryService,
    DatasetInfo $datasetInfo,
    MetastoreApiResponse $metastoreApiResponse,
    ConfigFactoryInterface $configFactory
  ) {
    $this->queryService = $queryService;
    $this->datasetInfo = $datasetInfo;
    $this->metastoreApiResponse = $metastoreApiResponse;
    $this->configFactory = $configFactory;
  }

  /**
   * Create controller object from dependency injection container.
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('dkan.datastore.query'),
      $container->get('dkan.common.dataset_info'),
      $container->get('dkan.metastore.api_response'),
      $container->get('config.factory')
    );
  }

  /**
   * Query a resource (or several, using joins), identified in the request body.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return Ilbee\CSVResponse\CSVResponse|Symfony\Component\HttpFoundation\JsonResponse
   *   The json or CSV response.
   */
  public function query(Request $request) {
    try {
      $datastoreQuery = $this->buildDatastoreQuery($request);
    }
    catch (\Exception $e) {
      return $this->getResponseFromException($e, 400);
    }
    try {
      $result = $this->queryService->runQuery($datastoreQuery);
    }
    catch (\Exception $e) {
      $code = (strpos($e->getMessage(), "Error retrieving") !== FALSE) ? 404 : 400;
      return $this->getResponseFromException($e, $code);
    }

    $dependencies = $this->extractMetastoreDependencies($datastoreQuery);
    return $this->formatResponse($datastoreQuery, $result, $dependencies, $request->query);
  }

  /**
   * Query a single resource, identified by resource or distribution ID.
   *
   * @param string $identifier
   *   The uuid of a resource.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The json response.
   */
  public function queryResource(string $identifier, Request $request) {
    try {
      $datastoreQuery = $this->buildDatastoreQuery($request, $identifier);
    }
    catch (\Exception $e) {
      return $this->getResponseFromException($e, 400);
    }
    try {
      $result = $this->queryService->runQuery($datastoreQuery);
    }
    catch (\Exception $e) {
      $code = (strpos($e->getMessage(), "Error retrieving") !== FALSE) ? 404 : 400;
      return $this->getResponseFromException($e, $code);
    }

    return $this->formatResponse($datastoreQuery, $result, ['distribution' => [$identifier]], $request->query);
  }

  /**
   * Query a single resource, identified by dataset ID and index.
   *
   * @param string $dataset
   *   The uuid of a dataset.
   * @param string $index
   *   The index of the resource in the dataset array.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The json response.
   */
  public function queryDatasetResource(string $dataset, string $index, Request $request) {
    $metadata = $this->datasetInfo->gather($dataset);
    if (!isset($metadata['latest_revision'])) {
      return $this->getResponse((object) ['message' => "No dataset found with the identifier $dataset"], 404);
    }
    if (!isset($metadata['latest_revision']['distributions'][$index]['distribution_uuid'])) {
      return $this->getResponse((object) ['message' => "No resource found at index $index"], 404);
    }
    $identifier = $metadata['latest_revision']['distributions'][$index]['distribution_uuid'];
    return $this->queryResource($identifier, $request);
  }

  /**
   * Format and return the result.
   *
   * Abstract method; override in specific implementations.
   *
   * @param Drupal\datastore\Service\DatastoreQuery $datastoreQuery
   *   A datastore query object.
   * @param RootedData\RootedJsonData $result
   *   The result of the datastore query.
   * @param array $dependencies
   *   A dependency array for use by \Drupal\metastore\MetastoreApiResponse.
   * @param \Symfony\Component\HttpFoundation\ParameterBag|null $params
   *   The parameter object from the request.
   */
  abstract public function formatResponse(
    DatastoreQuery $datastoreQuery,
    RootedJsonData $result,
    array $dependencies = [],
    ?ParameterBag $params = NULL
  );

  /**
   * Get metastore cache dependencies from a datastore query.
   *
   * @param \Drupal\datastore\Service\DatastoreQuery $datastoreQuery
   *   The datastore query object.
   *
   * @return array
   *   Dependency array for \Drupal\metastore\MetastoreApiResponse.
   */
  protected function extractMetastoreDependencies(DatastoreQuery $datastoreQuery): array {
    if (!isset($datastoreQuery->{'$.resources'})) {
      return [];
    }
    $dependencies = ['distribution' => []];
    foreach ($datastoreQuery->{'$.resources'} as $resource) {
      $dependencies['distribution'][] = $resource['id'];
    }
    return $dependencies;
  }

  /**
   * Normalize a resource query to a standard datastore query.
   *
   * When querying a resource directly, the payload does not have a "resources"
   * array. But one needs to be inferred from the request params and added
   * before execution.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The client request.
   * @param mixed $identifier
   *   Resource identifier to query against, if supplied via path.
   */
  protected function buildDatastoreQuery(Request $request, $identifier = NULL) {
    $json = static::getPayloadJson($request);
    $data = json_decode($json);
    $this->additionalPayloadValidation($data, $identifier);
    if ($identifier) {
      $resource = (object) ["id" => $identifier, "alias" => "t"];
      $data->resources = [$resource];
    }
    return new DatastoreQuery(json_encode($data), $this->getRowsLimit());
  }

  /**
   * Run some additional validation on incoming request.
   *
   * @param object $data
   *   The decoded request data.
   * @param mixed $identifier
   *   Resource identifier.
   */
  protected function additionalPayloadValidation($data, $identifier = NULL) {
    $this->checkForRowIdProperty($data);
    if (!empty($data->properties) && !empty($data->rowIds)) {
      throw new \Exception('The rowIds property cannot be set to true if you are requesting specific properties.');
    }
    if ($identifier && (!empty($data->resources) || !empty($data->joins))) {
      throw new \Exception('Joins are not available and resources should not be explicitly passed ' .
        'when using the resource query endpoint. Try /api/1/datastore/query.');
    }
  }

  /**
   * Check if the record_number is being explicitly requested.
   *
   * @param object $data
   *   The query object.
   */
  protected function checkForRowIdProperty($data) {
    if (!isset($data->properties)) {
      return;
    }
    $hasProperty = FALSE;
    foreach ($data->properties as $property) {
      $hasProperty = (is_string($property) && $property == 'record_number');
      $hasProperty = $hasProperty ?: (isset($property->property) && $property->property == 'record_number');
      if ($hasProperty) {
        throw new \Exception('The record_number property is for internal use and cannot be requested ' .
        'directly. Set rowIds to true and remove properties from your query to see the full table ' .
        'with row IDs.');
      }
    }
  }

  /**
   * Get the rows limit for datastore queries.
   *
   * @return int
   *   API rows limit.
   */
  protected function getRowsLimit(): int {
    return (int) ($this->configFactory->get('datastore.settings')->get('rows_limit') ?: self::DEFAULT_ROWS_LIMIT);
  }

  /**
   * Get the JSON string from a request, with type coercion applied.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The HTTP request.
   * @param string|null $schema
   *   Optional JSON schema string, used to cast data types.
   *
   * @return string
   *   Normalized and type-casted JSON string.
   */
  public static function getPayloadJson(Request $request, $schema = NULL) {
    $schema = $schema ?? file_get_contents(__DIR__ . "/../../docs/query.json");
    $payloadJson = static::getJson($request);
    $payloadJson = static::fixTypes($payloadJson, $schema);
    return $payloadJson;
  }

  /**
   * Just get the JSON string from the request.
   *
   * @param Symfony\Component\HttpFoundation\Request $request
   *   Symfony HTTP request object.
   *
   * @return string
   *   JSON string.
   *
   * @throws UnexpectedValueException
   *   When an unsupported HTTP method is passed.
   */
  public static function getJson(Request $request) {
    $method = $request->getRealMethod();
    switch ($method) {
      case "POST":
      case "PUT":
      case "PATCH":
        return $request->getContent();

      case "GET":
        return json_encode((object) $request->query->all());

      default:
        throw new \UnexpectedValueException("Only POST, PUT, PATCH and GET requests can be normalized.");
    }
  }

  /**
   * Cast data types in the JSON object according to a schema.
   *
   * @param string $json
   *   JSON string.
   * @param string $schema
   *   JSON Schema string.
   *
   * @return string
   *   JSON string with type coercion applied.
   */
  public static function fixTypes($json, $schema) {
    $data = json_decode($json);
    $validator = new Validator();
    $validator->coerce($data, json_decode($schema));
    return json_encode($data, JSON_PRETTY_PRINT);
  }

}
