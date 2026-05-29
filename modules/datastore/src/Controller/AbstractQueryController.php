<?php

namespace Drupal\datastore\Controller;

use Drupal\common\DatasetInfo;
use Drupal\common\JsonResponseTrait;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\datastore\Service\DatastoreQuery;
use Drupal\datastore\Service\Query as QueryService;
use Drupal\metastore\MetastoreApiResponse;
use Drupal\Core\State\StateInterface;
use JsonSchema\Validator;
use RootedData\RootedJsonData;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

/**
 * Abstract Controller providing base functionality used to query datastores.
 *
 * @package Drupal\datastore
 */
abstract class AbstractQueryController implements ContainerInjectionInterface {
  use JsonResponseTrait;

  const DEGRADE_MODE_RETRY_AFTER = 120;

  /**
   * Datastore query service.
   */
  protected QueryService $queryService;

  /**
   * DatasetInfo Service.
   */
  protected DatasetInfo $datasetInfo;

  /**
   * ConfigFactory object.
   */
  protected ConfigFactoryInterface $configFactory;

  /**
   * Metastore API response.
   */
  protected MetastoreApiResponse $metastoreApiResponse;

  /**
   * State service.
   */
  protected StateInterface $state;

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
    ConfigFactoryInterface $configFactory,
    StateInterface $state,
  ) {
    $this->queryService = $queryService;
    $this->datasetInfo = $datasetInfo;
    $this->metastoreApiResponse = $metastoreApiResponse;
    $this->configFactory = $configFactory;
    $this->state = $state;
  }

  /**
   * Create controller object from dependency injection container.
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('dkan.datastore.query'),
      $container->get('dkan.common.dataset_info'),
      $container->get('dkan.metastore.api_response'),
      $container->get('config.factory'),
      $container->get('state'),
    );
  }

  /**
   * Query a resource (or several, using joins), identified in the request body.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Ilbee\CSVResponse\CSVResponse|\Symfony\Component\HttpFoundation\JsonResponse
   *   The json or CSV response.
   */
  public function query(Request $request) {
    try {
      $datastoreQuery = $this->buildDatastoreQuery($request);
    }
    catch (HttpException $e) {
      return $this->getResponseFromException($e, $e->getStatusCode());
    }
    catch (\Exception $e) {
      return $this->getResponseFromException($e, 400);
    }
    try {
      $result = $this->queryService->runQuery($datastoreQuery);
    }
    catch (\Exception $e) {
      $code = (str_contains($e->getMessage(), "Error retrieving")) ? 404 : 400;
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
    catch (HttpException $e) {
      return $this->getResponseFromException($e, $e->getStatusCode());
    }
    catch (\Exception $e) {
      return $this->getResponseFromException($e, 400);
    }
    try {
      $result = $this->queryService->runQuery($datastoreQuery);
    }
    catch (\Exception $e) {
      $code = (str_contains($e->getMessage(), "Error retrieving")) ? 404 : 400;
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
    $distribution_uuid = $this->datasetInfo->getDistributionUuid($dataset, $index);

    if (empty($distribution_uuid)) {
      return $this->getResponse((object) ['message' => "No resource found for dataset $dataset at index $index"], 404);
    }

    return $this->queryResource($distribution_uuid, $request);
  }

  /**
   * Format and return the result.
   *
   * Abstract method; override in specific implementations.
   *
   * @param \Drupal\datastore\Service\DatastoreQuery $datastoreQuery
   *   A datastore query object.
   * @param \RootedData\RootedJsonData $result
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
    ?ParameterBag $params = NULL,
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
  protected function buildDatastoreQuery(Request $request, mixed $identifier = NULL) {
    $json = static::getPayloadJson($request);
    $data = json_decode($json);
    $this->assertDegradedModeAllowed($data);
    $this->additionalPayloadValidation($data, $identifier);
    if ($identifier) {
      $resource = (object) ["id" => $identifier, "alias" => "t"];
      $data->resources = [$resource];
    }
    // Force schema if CSV.
    if (($data->format ?? NULL) == 'csv') {
      $data->schema = TRUE;
    }
    return new DatastoreQuery(json_encode($data), $this->getRowsLimit());
  }

  /**
   * Block expensive queries when degraded performance mode is enabled.
   *
   * @param object $data
   *   The decoded request data.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   When a blocked query is detected.
   */
  protected function assertDegradedModeAllowed(object $data): void {
    if (!$this->state->get('dkan_datastore.degraded_performance', FALSE)) {
      return;
    }

    $blocked = FALSE;
    if (!empty($data->conditions)) {
      $blocked = TRUE;
    }
    if (!empty($data->joins)) {
      $blocked = TRUE;
    }
    if (!empty($data->groupings)) {
      $blocked = TRUE;
    }
    if (!empty($data->sorts)) {
      $blocked = TRUE;
    }
    if ((int) ($data->offset ?? 0) !== 0) {
      $blocked = TRUE;
    }

    if ($blocked) {
      throw new ServiceUnavailableHttpException(
        static::DEGRADE_MODE_RETRY_AFTER,
        'Datastore queries are temporarily limited due to high server load. Remove conditions, joins, groupings, sorts, and offsets to retry.'
      );
    }
  }

  /**
   * Run some additional validation on incoming request.
   *
   * @param object $data
   *   The decoded request data.
   * @param mixed $identifier
   *   Resource identifier.
   */
  protected function additionalPayloadValidation($data, mixed $identifier = NULL) {
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
    $schema ??= file_get_contents(__DIR__ . "/../../docs/query.json");
    $payloadJson = static::getJson($request);
    return static::fixTypes($payloadJson, $schema);
  }

  /**
   * Just get the JSON string from the request.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Symfony HTTP request object.
   *
   * @return string
   *   JSON string.
   *
   * @throws \UnexpectedValueException
   *   When an unsupported HTTP method is passed.
   */
  public static function getJson(Request $request) {
    $method = $request->getRealMethod();
    return match ($method) {
      'POST', 'PUT', 'PATCH' => $request->getContent(),
      'GET' => json_encode((object) $request->query->all()),
      default => throw new \UnexpectedValueException('Only POST, PUT, PATCH and GET requests can be normalized.'),
    };
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

    if ($data !== NULL) {
      $validator = new Validator();
      $validator->coerce($data, json_decode($schema));
      return json_encode($data, JSON_PRETTY_PRINT);
    }

    throw new \InvalidArgumentException("Invalid JSON");
  }

  /**
   * Build a CSV header row based on a query and result.
   *
   * @param \Drupal\datastore\Service\DatastoreQuery $datastoreQuery
   *   A datastore query object.
   * @param \RootedData\RootedJsonData $result
   *   The result of the datastore query.
   *
   * @return array
   *   Array of strings for a CSV header row.
   */
  protected function getHeaderRow(DatastoreQuery $datastoreQuery, RootedJsonData &$result) {
    $config = $this->configFactory->get('metastore.settings')->get('csv_headers_mode');
    $schema_fields = $result->{'$.schema..fields'}[0] ?? [];
    if (empty($schema_fields)) {
      throw new \DomainException("Could not generate header for CSV.");
    }
    if (empty($datastoreQuery->{'$.properties'})) {
      return array_keys($schema_fields);
    }

    $header_row = [];
    foreach ($datastoreQuery->{'$.properties'} ?? [] as $property) {
      $normalized_prop = $this->propToString($property, $datastoreQuery);
      if ($config == "machine_names") {
        $header_row[] = $normalized_prop ?? ($schema_fields[$normalized_prop]['description'] ?? FALSE);
      }
      else {
        $header_row[] = $schema_fields[$normalized_prop]['description'] ?? $normalized_prop;
      }
    }

    return $header_row;
  }

  /**
   * Transform any property into a string for mapping to schema.
   *
   * @param string|array $property
   *   A property from a DataStore Query.
   *
   * @return string
   *   String version of property.
   */
  protected function propToString(string|array $property): string {
    if (is_string($property)) {
      return $property;
    }
    elseif (isset($property['property'])) {
      return $property['property'];
    }
    elseif (isset($property['alias'])) {
      return $property['alias'];
    }
    else {
      throw new \DomainException("Invalid property: " . print_r($property, TRUE));
    }
  }

}
