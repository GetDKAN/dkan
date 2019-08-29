<?php

namespace Drupal\dkan_harvest\Controller;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class Api.
 *
 * @package Drupal\dkan_harvest\Controller
 */
class Api implements ContainerInjectionInterface {

  /**
   * Request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  private $requestStack;

  /**
   * Harvest.
   *
   * @var \Drupal\dkan_harvest\Harvester
   */
  private $harvester;

  /**
   * Inherited.
   *
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new Api($container);
  }

  /**
   * Api constructor.
   */
  public function __construct(ContainerInterface $container) {
    $this->requestStack = $container->get('request_stack');
    $this->harvester = $container->get('dkan_harvest.service');
  }

  /**
   * List harvest ids.
   */
  public function index() {

    try {

      $rows = $this->harvester
        ->getAllHarvestIds();

      return new JsonResponse(
            $rows,
            200,
            ["Access-Control-Allow-Origin" => "*"]
      );
    }
    catch (\Exception $e) {
      return $this->exceptionJsonResponse($e);
    }
  }

  /**
   * Register a new harvest.
   */
  public function register() {
    try {
      $harvest_plan = $this->requestStack->getCurrentRequest()->getContent();
      $plan = json_decode($harvest_plan);
      $identifier = $this->harvester
        ->registerHarvest($plan);

      return new JsonResponse(
            (object) [
              "identifier" => $identifier,
            ],
            200,
            [
              "Access-Control-Allow-Origin" => "*",
            ]
      );
    }
    catch (\Exception $e) {
      return $this->exceptionJsonResponse($e);
    }
  }

  /**
   * Deregister a harvest.
   */
  public function deregister($id) {

    try {

      $this->harvester
        ->deregisterHarvest($id);

      return new JsonResponse(
            (object) [
              "identifier" => $id,
            ],
            200,
            ["Access-Control-Allow-Origin" => "*"]
      );
    }
    catch (\Exception $e) {
      return $this->exceptionJsonResponse($e);
    }
  }

  /**
   * Runs harvest.
   *
   * @param string $id
   *   The harvest id.
   */
  public function run($id) {
    try {

      $result = $this->harvester
        ->runHarvest($id);

      return new JsonResponse(
            (object) [
              "identifier" => $id,
              "result"     => $result,
            ],
            200,
            ["Access-Control-Allow-Origin" => "*"]
      );
    }
    catch (\Exception $e) {
      return $this->exceptionJsonResponse($e);
    }
  }

  /**
   * Gives list of previous runs for a harvest id.
   *
   * @param string $id
   *   The harvest id.
   */
  public function info($id) {

    try {

      $response = array_keys($this->harvester
        ->getAllHarvestRunInfo($id));

      return new JsonResponse(
            $response,
            200,
            ["Access-Control-Allow-Origin" => "*"]
      );
    }
    catch (\Exception $e) {
      return $this->exceptionJsonResponse($e);
    }
  }

  /**
   * Gives information about a single previous harvest run.
   *
   * @param string $id
   *   The harvest id.
   * @param string $run_id
   *   The run's id.
   */
  public function infoRun($id, $run_id) {

    try {

      $response = $this->harvester
        ->getHarvestRunInfo($id, $run_id);

      return new JsonResponse(
            $response,
            200,
            ["Access-Control-Allow-Origin" => "*"]
      );
    }
    catch (\Exception $e) {
      return $this->exceptionJsonResponse($e);
    }
  }

  /**
   * Reverts harvest.
   *
   * @param string $id
   *   The source to revert.
   */
  public function revert($id) {
    try {

      $result = $this->harvester
        ->revertHarvest($id);

      return new JsonResponse(
            (object) [
              "identifier" => $id,
              'result'     => $result,
            ],
            200,
            [
              "Access-Control-Allow-Origin" => "*",
            ]
      );
    }
    catch (\Exception $e) {
      return $this->exceptionJsonResponse($e);
    }
  }

  /**
   * Private.
   */
  private function exceptionJsonResponse(\Exception $e) {
    return new JsonResponse(
      (object) [
        'message' => $e->getMessage(),
      ],
      500
    );
  }

}
