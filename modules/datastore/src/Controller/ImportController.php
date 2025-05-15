<?php

namespace Drupal\datastore\Controller;

use Drupal\common\DataResource;
use Drupal\common\JsonResponseTrait;
use Drupal\Component\Uuid\Uuid;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\datastore\DatastoreService;
use Drupal\datastore\Service\Info\ImportInfoList;
use Drupal\metastore\MetastoreApiResponse;
use Drupal\metastore\Reference\ReferenceLookup;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class Api.
 *
 * @package Drupal\datastore
 *
 * @codeCoverageIgnore
 */
class ImportController implements ContainerInjectionInterface {
  use JsonResponseTrait;

  /**
   * Datastore Service.
   */
  protected DatastoreService $datastoreService;

  /**
   * Metastore API response service.
   */
  protected MetastoreApiResponse $metastoreApiResponse;

  /**
   * Reference lookup service.
   */
  protected ReferenceLookup $referenceLookup;

  /**
   * Import info list service.
   */
  protected ImportInfoList $importInfoList;

  /**
   * Api constructor.
   */
  public function __construct(
    DatastoreService $datastoreService,
    MetastoreApiResponse $metastoreApiResponse,
    ReferenceLookup $referenceLookup,
    ImportInfoList $importInfoList
  ) {
    $this->datastoreService = $datastoreService;
    $this->metastoreApiResponse = $metastoreApiResponse;
    $this->referenceLookup = $referenceLookup;
    $this->importInfoList = $importInfoList;
  }

  /**
   * Create controller object from dependency injection container.
   */
  public static function create(ContainerInterface $container) {
    return new ImportController(
      $container->get('dkan.datastore.service'),
      $container->get('dkan.metastore.api_response'),
      $container->get('dkan.metastore.reference_lookup'),
      $container->get('dkan.datastore.import_info_list')
    );
  }

  /**
   * Returns the dataset along with datastore headers and statistics.
   *
   * @param string $identifier
   *   Resource or metastore (distribution) identifier.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The json response.
   */
  public function summary(string $identifier, Request $request) {
    try {
      $data = $this->datastoreService->summary($identifier);
      $dependencies = $this->getDependencies($identifier);
      return $this->metastoreApiResponse->cachedJsonResponse($data, 200, $dependencies, $request->query);
    }
    catch (\Exception) {
      $exception = new \Exception("A datastore for resource {$identifier} does not exist.");
      return $this->getResponseFromException($exception, 404);
    }
  }

  /**
   * Get cache dependencies for an identifier.
   *
   * @param string $identifier
   *   Resource or distribution identifier.
   *
   * @return array
   *   Dependency array for \Drupal\metastore\MetastoreApiResponse.
   */
  private function getDependencies($identifier) {
    // If a proper UUID, probably a distribution.
    if (Uuid::isValid($identifier)) {
      $distributions = [$identifier];
    }
    elseif (strlen($identifier) == 52) {
      $distributions = $this->referenceLookup->getReferencers('distribution', $identifier, 'downloadURL');
    }
    elseif (strlen($identifier) == 44) {
      $resourceId = "{$identifier}__source";
      $distributions = $this->referenceLookup->getReferencers('distribution', $resourceId, 'downloadURL');
    }
    else {
      $distributions = [];
    }
    return empty($distributions) ? ['distribution'] : ['distribution' => $distributions];
  }

  /**
   * Import.
   */
  public function import(Request $request) {

    $payloadJson = $request->getContent();
    $payload = json_decode($payloadJson);

    if (isset($payload->resource_ids)) {
      return $this->importMultiple($payload->resource_ids);
    }

    if (!isset($payload->resource_id)) {
      return $this->getResponseFromException(new \Exception("Invalid payload."));
    }

    try {
      $resourceId = $payload->resource_id;
      $identifier = NULL;
      $version = NULL;
      [$identifier, $version] = DataResource::getIdentifierAndVersion($resourceId);
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
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Json response.
   */
  public function deleteMultiple(Request $request) {
    $payloadJson = $request->getContent();
    $payload = json_decode($payloadJson);

    if (!isset($payload->resource_ids)) {
      return $this->getResponseFromException(new \Exception("Invalid payload."));
    }

    $identifiers = $payload->resource_ids;

    $responses = [];
    foreach ($identifiers as $identifier) {
      $responses[$identifier] = json_decode((string) $this->delete($identifier)->getContent());
    }

    return $this->getResponse($responses);
  }

  /**
   * Returns a list of import jobs and data about their status.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The json response.
   */
  public function list(Request $request) {
    try {
      $data = $this->importInfoList->buildList();
      return $this->metastoreApiResponse->cachedJsonResponse($data, 200, ['distribution'], $request->query);
    }
    catch (\Exception $e) {
      return $this->getResponseFromException(
        new \Exception("No importer data was returned. {$e->getMessage()}"),
        404
      );
    }
  }

}
