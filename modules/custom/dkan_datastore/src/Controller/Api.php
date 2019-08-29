<?php

namespace Drupal\dkan_datastore\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\dkan_datastore\Service\Datastore;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class Api.
 *
 * @package Drupal\dkan_datastore\Controller
 */
class Api implements ContainerInjectionInterface {
  /**
   * Datastore Service.
   *
   * @var \Drupa\dkan_datastore\Service\Datastore
   */
  protected $datastoreService;

  /**
   * Api constructor.
   */
  public function __construct(Datastore $datastoreService) {
    $this->datastoreService = $datastoreService;
  }

  /**
   * Create controller object from dependency injection container.
   */
  public static function create(ContainerInterface $container) {
    $datastoreService = $container->get('dkan_datastore.service');
    return new Api($datastoreService);
  }

  /**
   * Returns the dataset along with datastore headers and statistics.
   *
   * @param string $uuid
   *   Identifier.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The json response.
   */
  public function summary($uuid) {
    try {
      $data = $this->datastoreService->getStorage($uuid)->getSummary();
      return $this->successResponse($data);
    }
    catch (\Exception $e) {
      return $this->exceptionResponse($e);
    }
  }

  /**
   * Import.
   *
   * @param string $uuid
   *   The uuid of a dataset.
   * @param bool $deferred
   *   Whether or not the process should be deferred to a queue.
   */
  public function import($uuid, $deferred = FALSE) {

    try {
      $results = $this->datastoreService->import($uuid, $deferred);
      return $this->successResponse($results);
    }
    catch (\Exception $e) {
      return $this->exceptionResponse($e);
    }
  }

  /**
   * Drop.
   *
   * @param string $uuid
   *   The uuid of a dataset.
   */
  public function delete($uuid) {
    try {
      $this->datastoreService->drop($uuid);
      return $this->successResponse((object) ["identifier" => $uuid]);
    }
    catch (\Exception $e) {
      return $this->exceptionResponse($e);
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
  private function exceptionResponse(\Exception $e) {
    return new JsonResponse((object) ['message' => $e->getMessage()], 500);
  }

}
