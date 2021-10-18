<?php

namespace Drupal\datastore\Controller;

use Drupal\common\DatasetInfo;
use Drupal\datastore\Service\DatastoreQuery;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\common\JsonResponseTrait;
use RootedData\RootedJsonData;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\datastore\Service;
use Drupal\metastore\MetastoreApiResponse;
use JsonSchema\Validator;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class Api.
 *
 * @package Drupal\datastore
 */
abstract class AbstractQueryController implements ContainerInjectionInterface {
  use JsonResponseTrait;

  /**
   * Datastore Service.
   *
   * @var \Drupal\datastore\Service
   */
  protected $datastoreService;

  /**
   * Request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  private $requestStack;

  /**
   * DatasetInfo Service.
   *
   * @var \Drupal\common\DatasetInfo
   */
  protected $datasetInfo;

  /**
   * ConfigFactory object.
   *
   * @var Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configFactory;

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
    Service $datastoreService,
    RequestStack $requestStack,
    DatasetInfo $datasetInfo,
    MetastoreApiResponse $metastoreApiResponse,
    ConfigFactoryInterface $configFactory
  ) {
    $this->datastoreService = $datastoreService;
    $this->requestStack = $requestStack;
    $this->datasetInfo = $datasetInfo;
    $this->metastoreApiResponse = $metastoreApiResponse;
    $this->configFactory = $configFactory;
  }

  /**
   * Create controller object from dependency injection container.
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('dkan.datastore.service'),
      $container->get('request_stack'),
      $container->get('dkan.common.dataset_info'),
      $container->get('dkan.metastore.api_response'),
      $container->get('config.factory')
    );
  }

  /**
   * Perform a query on one or more datastore resources.
   *
   * @return Ilbee\CSVResponse\CSVResponse|Symfony\Component\HttpFoundation\JsonResponse
   *   The json or CSV response.
   */
  public function query() {
    $request = $this->requestStack->getCurrentRequest();
    $payloadJson = static::getPayloadJson($request);

    try {
      $datastoreQuery = new DatastoreQuery($payloadJson, $this->getRowsLimit());
    }
    catch (\Exception $e) {
      return $this->getResponseFromException($e, 400);
    }

    $result = $this->datastoreService->runQuery($datastoreQuery);

    $dependencies = $this->extractMetastoreDependencies($datastoreQuery);
    return $this->formatResponse($datastoreQuery, $result, $dependencies, $request->query);
  }

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
   * Perform a query on a single datastore resource.
   *
   * @param string $identifier
   *   The uuid of a resource.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The json response.
   */
  public function queryResource(string $identifier) {
    $request = $this->requestStack->getCurrentRequest();
    $payloadJson = static::getPayloadJson($request);

    try {
      $this->prepareQueryResourcePayload($payloadJson, $identifier);
    }
    catch (\Exception $e) {
      return $this->getResponseFromException(
        new \Exception("Invalid query JSON: {$e->getMessage()}"),
        400
      );
    }
    try {
      $datastoreQuery = new DatastoreQuery($payloadJson, $this->getRowsLimit());
      $result = $this->datastoreService->runQuery($datastoreQuery);
    }
    catch (\Exception $e) {
      $code = (strpos($e->getMessage(), "Error retrieving") !== FALSE) ? 404 : 400;
      return $this->getResponseFromException($e, $code);
    }

    return $this->formatResponse($datastoreQuery, $result, ['distribution' => [$identifier]], $request->query);
  }

  /**
   * Perform a query on a single datastore resource.
   *
   * @param string $dataset
   *   The uuid of a dataset.
   * @param string $index
   *   The index of the resource in the dataset array.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The json response.
   */
  public function queryDatasetResource(string $dataset, string $index) {
    $metadata = $this->datasetInfo->gather($dataset);
    if (!isset($metadata['latest_revision'])) {
      return $this->getResponse((object) ['message' => "No dataset found with the identifier $dataset"], 400);
    }
    if (!isset($metadata['latest_revision']['distributions'][$index]['distribution_uuid'])) {
      return $this->getResponse((object) ['message' => "No resource found at index $index"], 400);
    }
    $identifier = $metadata['latest_revision']['distributions'][$index]['distribution_uuid'];
    return $this->queryResource($identifier);
  }

  /**
   * Normalize the simplified resource query to a standard datastore query.
   *
   * @param string $json
   *   A JSON payload.
   * @param mixed $identifier
   *   Resource identifier to query against.
   */
  protected function prepareQueryResourcePayload(&$json, $identifier) {
    $data = json_decode($json);
    if (json_last_error() !== JSON_ERROR_NONE) {
      throw new \Exception(json_last_error_msg());
    }
    if (!empty($data->resources) || !empty($data->joins)) {
      throw new \Exception("Joins are not available and "
        . "resources should not be explicitly passed when using the resource "
        . "query endpoint. Try /api/1/datastore/query.");
    }
    $resource = (object) ["id" => $identifier, "alias" => "t"];
    $data->resources = [$resource];
    $json = json_encode($data);
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
   * @param Symfony\Component\HttpFoundation\Request $request
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
