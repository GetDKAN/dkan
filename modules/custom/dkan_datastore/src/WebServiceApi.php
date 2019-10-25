<?php

namespace Drupal\dkan_datastore;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class Api.
 *
 * @package Drupal\dkan_datastore
 *
 * @codeCoverageIgnore
 */
class WebServiceApi implements ContainerInjectionInterface {
  /**
   * Datastore Service.
   *
   * @var \Drupa\dkan_datastore\Service
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
    $datastoreService = $container->get('dkan_datastore.service');
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
      $storage = $this->datastoreService->getStorage($identifier);
      if ($storage) {
        $data = $storage->getSummary();
        return $this->successResponse($data);
      }
      throw new \Exception("no storage");
    }
    catch (\Exception $e) {
      return $this->exceptionResponse(
        new \Exception("A datastore for resource {$identifier} does not exist."),
        404
      );
    }
  }

  /**
   * Import.
   */
  public function import() {

    $payloadJson = $this->requestStack->getCurrentRequest()->getContent();
    $payload = json_decode($payloadJson);
    if (!isset($payload->resource_id)) {
      return $this->exceptionResponse(new \Exception("Invalid payload."));
    }

    try {
      $results = $this->datastoreService->import($payload->resource_id, FALSE);
      return $this->successResponse($results);
    }
    catch (\Exception $e) {
      return $this->exceptionResponse($e);
    }
  }

  /**
   * Drop.
   *
   * @param string $identifier
   *   The uuid of a dataset.
   */
  public function delete($identifier) {
    try {
      $this->datastoreService->drop($identifier);
      return $this->successResponse(
        [
          "identifier" => $identifier,
          "message" => "The datastore for resource {$identifier} was succesfully dropped.",
        ]
      );
    }
    catch (\Exception $e) {
      return $this->exceptionResponse($e);
    }
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
      return $this->successResponse($data);
    }
    catch (\Exception $e) {
      return $this->exceptionResponse(
        new \Exception("No importer data was returned. {$e->getMessage()}"),
        404
      );
    }
  }

  /**
   * Private.
   */
  private function successResponse($message) {
    return new JsonResponse($message, 200, ["Access-Control-Allow-Origin" => "*"]);
  }

  /**
   * Private.
   */
  private function exceptionResponse(\Exception $e, $code = 500) {
    return new JsonResponse((object) ['message' => $e->getMessage()], $code);
  }

}
