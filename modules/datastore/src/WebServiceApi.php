<?php

namespace Drupal\datastore;

use Drupal\common\Resource;
use Drupal\datastore\Service\DatastoreQuery;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\common\JsonResponseTrait;

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

      [$id, $version] = Resource::getIdentifierAndVersion($identifier);
      $storage = $this->datastoreService
        ->getStorage($id, $version);

      if ($storage) {
        $data = $storage->getSummary();
        return $this->getResponse($data);
      }
      throw new \Exception("no storage");
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
      [$identifier, $version] = Resource::getIdentifierAndVersion($resourceId);
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
   * Returns a list of import jobs and data about their status.
   *
   * @param string $identifier
   *   The uuid of a resource.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The json response.
   */
  public function query($identifier) {

    $payloadJson = $this->requestStack->getCurrentRequest()->getContent();
    try {
      $datastoreQuery = DatastoreQuery::hydrate($payloadJson);
    }
    catch (\Exception $e) {
      return $this->getResponseFromException(
        new \Exception("Invalid query JSON: {$e->getMessage()}"),
        400
      );
    }
    $datastoreQuery->resource = $identifier;
    try {
      $result = $this->datastoreService->runQuery($datastoreQuery);
    }
    catch (\Exception $e) {
      return $this->getResponseFromException($e);
    }

    return $this->getResponse($result, 200);
  }

  /**
   * Export a CSV of query results.
   */
  public function buildCsv(array $keys, array $data) {
    // Use PHP's built in file handler functions to create a temporary file.
    $handle = fopen('php://temp', 'w+');

    // Set up the header as the first line of the CSV file.
    // Blank strings are used for multi-cell values where there is a count of
    // the "keys" and a list of the keys with the count of their usage.
    $header = [];
    foreach ($keys as $key) {
      $header[] = $key;
    }
    // Add the header as the first line of the CSV.
    fputcsv($handle, $header);

    // Iterate through the data.
    foreach ($data as $row) {
      fputcsv($handle, $row);
    }
    // Reset where we are in the CSV.
    rewind($handle);

    // Retrieve the data from the file handler.
    $csv_data = stream_get_contents($handle);

    // Close the file handler since we don't need it anymore.
    // We are not storing this file anywhere in the filesystem.
    fclose($handle);

    // Once the data is built, we can return it as a response.
    $response = new Response();

    // By setting these 2 header options, the browser will see the URL
    // used by this Controller to return a CSV file called "export.csv".
    $response->headers->set('Content-Type', 'text/csv');
    $response->headers->set('Content-Disposition', 'attachment; filename="export.csv"');

    // This line physically adds the CSV data we created.
    $response->setContent($csv_data);

    return $response;
  }

}
