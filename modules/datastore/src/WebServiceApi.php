<?php

namespace Drupal\datastore;

use Drupal\common\Resource;
use Drupal\datastore\Service\DatastoreQuery;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\common\JsonResponseTrait;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Class Api.
 *
 * @package Drupal\datastore
 *
 * @codeCoverageIgnore
 */
class WebServiceApi implements ContainerInjectionInterface {
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
   * Reusable Query.
   *
   * @var \Drupal\datastore\Service\DatastoreQuery
   */
  protected $datastoreQuery;

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
    $datastoreService = $container->get('datastore.service');
    $requestStack = $container->get('request_stack');
    return new WebServiceApi($datastoreService, $requestStack);
  }

  /**
   * Returns the dataset along with datastore headers and statistics.
   *
   * @param string $identifier
   *   Identifier.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The json response.
   */
  public function summary($identifier) {
    try {
      $data = $this->datastoreService->summary($identifier);
      return $this->getResponse($data);
    }
    catch (\Exception $e) {
      $exception = new \Exception("A datastore for resource {$identifier} does not exist.");
      return $this->getResponseFromException($exception, 404);
    }
  }

  /**
   * Import.
   */
  public function import() {

    $payloadJson = $this->requestStack->getCurrentRequest()->getContent();
    $payload = json_decode($payloadJson);

    if (isset($payload->resource_ids)) {
      return $this->importMultiple($payload->resource_ids);
    }

    if (!isset($payload->resource_id)) {
      return $this->getResponseFromException(new \Exception("Invalid payload."));
    }

    try {
      $resourceId = $payload->resource_id;
      $identifier = NULL; $version = NULL;
      list($identifier, $version) = Resource::getIdentifierAndVersion($resourceId);
      $results = $this->datastoreService->import($identifier, FALSE, $version);
      return $this->getResponse($results);
    }
    catch (\Exception $e) {
      return $this->getResponseFromException($e);
    }
  }

  /**
   * Private.
   */
  private function importMultiple(array $resourceIds) {

    $responses = [];
    foreach ($resourceIds as $identifier) {
      try {
        $results = $this->datastoreService->import($identifier, TRUE);
        $responses[$identifier] = $results;
      }
      catch (\Exception $e) {
        $responses[$identifier] = $e->getMessage();
      }
    }

    return $this->getResponse($responses);
  }

  /**
   * Drop.
   *
   * @param string $identifier
   *   The uuid of a resource.
   */
  public function delete($identifier) {
    try {
      $this->datastoreService->drop($identifier);
      return $this->getResponse(
        [
          "identifier" => $identifier,
          "message" => "The datastore for resource {$identifier} was successfully dropped.",
        ]
      );
    }
    catch (\Exception $e) {
      return $this->getResponseFromException($e);
    }
  }

  /**
   * Drop multiples.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Json response.
   */
  public function deleteMultiple() {
    $payloadJson = $this->requestStack->getCurrentRequest()->getContent();
    $payload = json_decode($payloadJson);

    if (!isset($payload->resource_ids)) {
      return $this->getResponseFromException(new \Exception("Invalid payload."));
    }

    $identifiers = $payload->resource_ids;

    $responses = [];
    foreach ($identifiers as $identifier) {
      $responses[$identifier] = json_decode($this->delete($identifier)->getContent());
    }

    return $this->getResponse($responses);
  }

  /**
   * Returns a list of import jobs and data about their status.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The json response.
   */
  public function list() {
    try {
      $data = $this->datastoreService->list();
      return $this->getResponse($data);
    }
    catch (\Exception $e) {
      return $this->getResponseFromException(
        new \Exception("No importer data was returned. {$e->getMessage()}"),
        404
      );
    }
  }

  /**
   * Perform a query on one or more datastore resources.
   *
   * @return CsvResponse|JsonResponse
   *   The json or CSV response.
   */
  public function query() {
    $result = $this->getResults();
    $format = $this->requestStack->getCurrentRequest()->query->get('format');

    switch ($format) {
      case 'csv':
        return $this->formatCsv($result->{"$"});

      case 'json':
      default:
        return $this->getResponse($result->{"$"}, 200);
    }
  }

  /**
   * Perform a query with unlimited results.
   */
  public function fileQuery() {
    $result = $this->getResults();

    // @TODO: Streamed response for json
    return $this->processStreamedCsv($result->{"$"});
  }

  /**
   * Get data from service.
   *
   * @return object|RootedJsonData|JsonResponse
   *   Return data or an exception.
   */
  protected function getResults() {
    $payloadJson = $this->requestStack->getCurrentRequest()->getContent();

    try {
      $this->datastoreQuery = new DatastoreQuery($payloadJson);
      $result = $this->datastoreService->runQuery($this->datastoreQuery);
    }
    catch (\Exception $e) {
      return $this->getResponseFromException($e, 400);
    }

    return $result;
  }

  /**
   * Reformat and create CSV file.
   *
   * @param array $data
   *   Data result with buried header info.
   *
   * @return \Drupal\datastore\CsvResponse
   *   CSV file as a response.
   */
  protected function formatCsv(array $data) {
    $data = $this->addHeaderRow($data);
    $response = new CsvResponse($data['results'], 200);
    $response->setFilename('data.csv');
    return $response;
  }

  /**
   * Setup the Streamed Response callback.
   *
   * @param array $data
   *   Data result.
   *
   * @return \Symfony\Component\HttpFoundation\StreamedResponse
   *   Return the StreamedResponse object.
   */
  protected function processStreamedCsv(array $data) {
    $max = $this->datastoreQuery->{"$.limit"};

    // Disable extra queries.
    $this->datastoreQuery->{"$.count"} = FALSE;
    $this->datastoreQuery->{"$.schema"} = FALSE;

    $data = $this->addHeaderRow($data);
    $response = new StreamedResponse();
    $response->headers->set('Content-Type', 'text/csv');
    $response->headers->set('Content-Disposition', 'attachment; filename="data.csv"');
    $response->headers->set('X-Accel-Buffering', 'no');
    $response->setCallback(function () use (&$data, &$max) {
      $i = 1;
      set_time_limit(0);
      $handle = fopen('php://output', 'wb');

      $this->sendRows($handle, $data);
      $count = count($data['results']);
      // Count can be greater as we add a header row to the first time.
      while ($count >= $max) {
        $this->datastoreQuery->{"$.offset"} = $max * $i;
        $result = $this->datastoreService->runQuery($this->datastoreQuery);
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
  private function addHeaderRow(array $data) {
    $header_row = array_keys(reset($data['schema'])['fields']);
    if (is_array($header_row)) {
      array_unshift($data['results'], $header_row);
    }
    return $data;
  }

  /**
   * Perform a query on a single datastore resource.
   *
   * @param string $identifier
   *   The uuid of a resource.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The json response.
   */
  public function queryResource($identifier) {
    $payloadJson = $this->requestStack->getCurrentRequest()->getContent();
    try {
      $this->prepareQueryResourcePayload($payloadJson, $identifier);
      $datastoreQuery = new DatastoreQuery($payloadJson);
    }
    catch (\Exception $e) {
      return $this->getResponseFromException(
        new \Exception("Invalid query JSON: {$e->getMessage()}"),
        400
      );
    }
    try {
      $result = $this->datastoreService->runQuery($datastoreQuery);
    }
    catch (\Exception $e) {
      return $this->getResponseFromException($e);
    }

    return $this->getResponse($result, 200);
  }

  /**
   * Retrieve the datastore query schema.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The json response.
   */
  public function querySchema() {
    $schema = json_decode(file_get_contents(__DIR__ . "/../docs/query.json"), TRUE);
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
