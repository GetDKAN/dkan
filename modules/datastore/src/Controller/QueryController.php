<?php

namespace Drupal\datastore\Controller;

use Drupal\common\DatasetInfo;
use Drupal\datastore\Service\DatastoreQuery;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\common\JsonResponseTrait;
use Symfony\Component\HttpFoundation\StreamedResponse;
use RootedData\RootedJsonData;
use Drupal\common\Util\RequestParamNormalizer;
use Drupal\Core\Config\ConfigFactoryInterface;
use Ilbee\CSVResponse\CSVResponse as CsvResponse;
use Drupal\datastore\Service;
use Drupal\metastore\MetastoreApiResponse;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Class Api.
 *
 * @package Drupal\datastore
 */
class QueryController implements ContainerInjectionInterface {
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
  public const DEFAULT_ROWS_LIMIT = 500;

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
  public function query($stream = FALSE) {
    $request = $this->requestStack->getCurrentRequest();
    $payloadJson = RequestParamNormalizer::getFixedJson(
      $request,
      file_get_contents(__DIR__ . "/../../docs/query.json")
    );

    try {
      $datastoreQuery = new DatastoreQuery($payloadJson, $this->getRowsLimit());
    }
    catch (\Exception $e) {
      return $this->getResponseFromException($e, 400);
    }

    $result = $this->datastoreService->runQuery($datastoreQuery);

    if ($stream) {
      return $this->streamResponse($datastoreQuery, $result);
    }

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
  private function extractMetastoreDependencies(DatastoreQuery $datastoreQuery): array {
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
   * Return correct JSON or CSV response.
   *
   * @param Drupal\datastore\Service\DatastoreQuery $datastoreQuery
   *   A datastore query object.
   * @param RootedData\RootedJsonData $result
   *   The result of the datastore query.
   * @param array $dependencies
   *   A dependency array for use by \Drupal\metastore\MetastoreApiResponse.
   * @param \Symfony\Component\HttpFoundation\ParameterBag|null $params
   *   The parameter object from the request.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The json response.
   */
  public function formatResponse(
    DatastoreQuery $datastoreQuery,
    RootedJsonData $result,
    array $dependencies = [],
    ?ParameterBag $params = NULL
  ) {
    switch ($datastoreQuery->{"$.format"}) {
      case 'csv':
        $response = new CsvResponse($result->{"$.results"}, 'data', ',');
        return $this->addCacheHeaders($response);

      case 'json':
      default:
        return $this->metastoreApiResponse->cachedJsonResponse($result->{"$"}, 200, $dependencies, $params);
    }

  }

  /**
   * Stream an unlimited response as a file.
   *
   * @param \Drupal\datastore\Service\DatastoreQuery $datastoreQuery
   *   A datastore Query object.
   * @param \RootedData\RootedJsonData $result
   *   Query result.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   An HTTP response.
   */
  protected function streamResponse(DatastoreQuery $datastoreQuery, RootedJsonData $result) {
    switch ($datastoreQuery->{"$.format"}) {
      case 'csv':
        return $this->processStreamedCsv($datastoreQuery, $result);

      case 'json':
      default:
        return $this->getResponseFromException(
          new \UnexpectedValueException("Streaming not currently available for JSON responses"),
          400
        );
    }
  }

  /**
   * Set up the Streamed Response callback.
   *
   * @param \Drupal\datastore\Service\DatastoreQuery $datastoreQuery
   *   A datastore Query object.
   * @param RootedData\RootedJsonData $result
   *   Query result.
   *
   * @return \Symfony\Component\HttpFoundation\StreamedResponse
   *   Return the StreamedResponse object.
   */
  protected function processStreamedCsv(DatastoreQuery $datastoreQuery, RootedJsonData $result) {
    $data = $result->{"$"} ? $result->{"$"} : [];
    // Override limit, set to max.
    $max = $datastoreQuery->{"$.limit"} = $this->getRowsLimit();

    $lastIndex = (count($data['results']) - 1);
    $lastRowId = (int) $result->{"$.results[$lastIndex].record_number"};
    $conditionIndex = count($datastoreQuery->{"$.conditions"} ?? []);

    // Disable extra queries.
    $datastoreQuery->{"$.count"} = FALSE;
    $datastoreQuery->{"$.schema"} = FALSE;

    $this->addHeaderRow($data);
    $response = $this->initStreamedCsvResponse();
    $response->setCallback(function () use (&$data, &$max, $datastoreQuery, $lastRowId, $conditionIndex) {
      $i = 1;
      // $useLast = !empty($lastRowId);
      set_time_limit(0);
      $handle = fopen('php://output', 'wb');

      $this->sendRows($handle, $data);
      $count = count($data['results']);
      // Count can be greater as we add a header row to the first time.
      while ($count >= $max) {
        // $datastoreQuery->{"$.offset"} = $max * $i;
        $datastoreQuery->{"$.conditions[$conditionIndex]"} = ['property' => 'record_number', 'value' => $lastRowId, 'operator' => '>'];
        $result = $this->datastoreService->runQuery($datastoreQuery);
        $data = $result->{"$"};
        $this->sendRows($handle, $data);
        $i++;
        $count = count($data['results']);

        $lastIndex = $count - 1;
        $lastRowId = (int) $result->{"$.results[$lastIndex].record_number"};
        $conditionIndex = count($datastoreQuery->{"$.conditions"} ?? []);
      }
      fclose($handle);
    });
    return $response;
  }

  /**
   * Create initial streamed response object.
   *
   * @return Symfony\Component\HttpFoundation\StreamedResponse
   *   A streamed response object set up for data.csv file.
   */
  private function initStreamedCsvResponse($filename = "data.csv") {
    $response = new StreamedResponse();
    $response->headers->set('Content-Type', 'text/csv');
    $response->headers->set('Content-Disposition', "attachment; filename=\"$filename\"");
    $response->headers->set('X-Accel-Buffering', 'no');
    return $response;
  }

  /**
   * Loop through rows and send csv.
   *
   * @param resource $handle
   *   The file handler.
   * @param array $data
   *   Data to send.
   */
  private function sendRows($handle, array $data) {
    foreach ($data['results'] as $row) {
      fputcsv($handle, $row);
    }
    ob_flush();
    flush();
  }

  /**
   * Add the header row, from specified properties, if any, or the schema.
   */
  private function addHeaderRow(array &$data) {

    if (!empty($data['query']['properties'])) {
      $header_row = $data['query']['properties'];
    }
    else {
      $header_row = array_keys(reset($data['schema'])['fields']);
    }

    if (is_array($header_row)) {
      array_unshift($data['results'], $header_row);
    }
  }

  /**
   * Perform a query on a single datastore resource.
   *
   * @param string $identifier
   *   The uuid of a resource.
   * @param bool $stream
   *   Whether to return result as a streamed file.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The json response.
   */
  public function queryResource(string $identifier, bool $stream = FALSE) {
    $request = $this->requestStack->getCurrentRequest();
    $payloadJson = RequestParamNormalizer::getJson($request);
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
      $payloadJson = RequestParamNormalizer::fixTypes($payloadJson, file_get_contents(__DIR__ . "/../../docs/query.json"));
      $datastoreQuery = new DatastoreQuery($payloadJson, $this->getRowsLimit());
      $result = $this->datastoreService->runQuery($datastoreQuery);
    }
    catch (\Exception $e) {
      $code = (strpos($e->getMessage(), "Error retrieving") !== FALSE) ? 404 : 400;
      return $this->getResponseFromException($e, $code);
    }

    if ($stream) {
      return $this->streamResponse($datastoreQuery, $result);
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
   * @param bool $stream
   *   Whether to return result as a streamed file.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The json response.
   */
  public function queryDatasetResource(string $dataset, string $index, bool $stream = FALSE) {
    $metadata = $this->datasetInfo->gather($dataset);
    if (!isset($metadata['latest_revision'])) {
      return $this->getResponse((object) ['message' => "No dataset found with the identifier $dataset"], 400);
    }
    if (!isset($metadata['latest_revision']['distributions'][$index]['distribution_uuid'])) {
      return $this->getResponse((object) ['message' => "No resource found at index $index"], 400);
    }
    $identifier = $metadata['latest_revision']['distributions'][$index]['distribution_uuid'];
    return $this->queryResource($identifier, $stream);
  }

  /**
   * Retrieve the datastore query schema. Used by datastore.1.query.schema.get.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The json response.
   */
  public function querySchema() {
    $schema = json_decode(file_get_contents(__DIR__ . "/../../docs/query.json"), TRUE);
    return $this->getResponse($schema, 200);
  }

  /**
   * Normalize the simplified resource query to a standard datastore query.
   *
   * @param string $json
   *   A JSON payload.
   * @param mixed $identifier
   *   Resource identifier to query against.
   */
  private function prepareQueryResourcePayload(&$json, $identifier) {
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

}
