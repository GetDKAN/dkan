<?php

namespace Drupal\datastore\Controller;

use Drupal\datastore\Service\DatastoreQuery;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\common\JsonResponseTrait;
use Symfony\Component\HttpFoundation\StreamedResponse;
use RootedData\RootedJsonData;
use Drupal\common\Util\RequestParamNormalizer;
use Ilbee\CSVResponse\CSVResponse as CsvResponse;
use Drupal\datastore\Service;

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
   * Api constructor.
   */
  public function __construct(Service $datastoreService, RequestStack $requestStack) {
    $this->datastoreService = $datastoreService;
    $this->requestStack = $requestStack;
  }

  /**
   * Create controller object from dependency injection container.
   */
  public static function create(ContainerInterface $container) {
    $datastoreService = $container->get('dkan.datastore.service');
    $requestStack = $container->get('request_stack');
    return new QueryController($datastoreService, $requestStack);
  }

  /**
   * Perform a query on one or more datastore resources.
   *
   * @return Ilbee\CSVResponse\CSVResponse|Symfony\Component\HttpFoundation\JsonResponse
   *   The json or CSV response.
   */
  public function query($stream = FALSE) {
    $payloadJson = RequestParamNormalizer::getFixedJson(
      $this->requestStack->getCurrentRequest(),
      file_get_contents(__DIR__ . "/../../docs/query.json")
    );

    try {
      $datastoreQuery = new DatastoreQuery($payloadJson);
    }
    catch (\Exception $e) {
      return $this->getResponseFromException($e, 400);
    }

    $result = $this->datastoreService->runQuery($datastoreQuery);

    if ($stream) {
      return $this->streamResponse($datastoreQuery, $result);
    }

    return $this->formatResponse($datastoreQuery, $result);
  }

  /**
   * Return correct JSON or CSV response.
   *
   * @param Drupal\datastore\Service\DatastoreQuery $datastoreQuery
   *   A datastore query object.
   * @param RootedData\RootedJsonData $result
   *   The result of the datastore query.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The json response.
   */
  public function formatResponse(DatastoreQuery $datastoreQuery, RootedJsonData $result) {
    switch ($datastoreQuery->{"$.format"}) {
      case 'csv':
        return new CsvResponse($result->{"$.results"}, 'data', ',');

      case 'json':
      default:
        return $this->getResponse($result->{"$"}, 200);
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
    $max = $datastoreQuery->{"$.limit"};

    // Disable extra queries.
    $datastoreQuery->{"$.count"} = FALSE;
    $datastoreQuery->{"$.schema"} = FALSE;

    $this->addHeaderRow($data);
    $response = $this->initStreamedCsvResponse();
    $response->setCallback(function () use (&$data, &$max, $datastoreQuery) {
      $i = 1;
      set_time_limit(0);
      $handle = fopen('php://output', 'wb');

      $this->sendRows($handle, $data);
      $count = count($data['results']);
      // Count can be greater as we add a header row to the first time.
      while ($count >= $max) {
        $datastoreQuery->{"$.offset"} = $max * $i;
        $result = $this->datastoreService->runQuery($datastoreQuery);
        $data = $result->{"$"};
        $this->sendRows($handle, $data);
        $i++;
        $count = count($data['results']);
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
   * Add the header row.
   */
  private function addHeaderRow(array &$data) {
    $header_row = array_keys(reset($data['schema'])['fields']);
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
    $payloadJson = RequestParamNormalizer::getJson($this->requestStack->getCurrentRequest());
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
      $datastoreQuery = new DatastoreQuery($payloadJson);
      $result = $this->datastoreService->runQuery($datastoreQuery);
    }
    catch (\Exception $e) {
      return $this->getResponseFromException($e, 400);
    }

    if ($stream) {
      return $this->streamResponse($datastoreQuery, $result);
    }

    return $this->formatResponse($datastoreQuery, $result);
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

}
