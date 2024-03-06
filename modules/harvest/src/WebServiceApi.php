<?php

namespace Drupal\harvest;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class Api.
 *
 * @package Drupal\harvest\Controller
 *
 * @codeCoverageIgnore
 */
class WebServiceApi implements ContainerInjectionInterface {

  /**
   * Request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  private $requestStack;

  /**
   * Harvest.
   *
   * @var \Drupal\harvest\HarvestService
   */
  private $harvester;

  /**
   * Inherited.
   *
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new WebServiceApi($container->get('request_stack'), $container->get('dkan.harvest.service'));
  }

  /**
   * Api constructor.
   */
  public function __construct(RequestStack $requestStack, HarvestService $service) {
    $this->requestStack = $requestStack;
    $this->harvester = $service;
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
   * Get a single harvest plan.
   *
   * @param string $identifier
   *   A harvest plan id.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Json response.
   */
  public function getPlan($identifier) {
    try {
      $plan = $this->harvester
        ->getHarvestPlan($identifier);

      return new JsonResponse(
        json_decode($plan),
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
  public function deregister($identifier) {

    try {

      $this->harvester
        ->deregisterHarvest($identifier);

      return new JsonResponse(
            (object) [
              "identifier" => $identifier,
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
   */
  public function run() {
    try {
      $payloadJson = $this->requestStack->getCurrentRequest()->getContent();
      $payload = json_decode($payloadJson);
      if (!isset($payload->plan_id)) {
        $return = [
          "message" => "Invalid payload.",
          "documentation" => "/api/1/harvest",
        ];
        return $this->jsonResponse($return, 422);
      }

      $id = $payload->plan_id;
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
   */
  public function info() {

    try {
      $id = $this->requestStack->getCurrentRequest()->get('plan');
      if (empty($id)) {
        return new JsonResponse(
          ["message" => "Missing 'plan' query parameter value"],
          400,
          ["Access-Control-Allow-Origin" => "*"]
        );
      }

      $response = $this->harvester
        ->getAllHarvestRunInfo($id);

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
   * @param string $identifier
   *   The run's id.
   */
  public function infoRun($identifier) {

    $id = $this->requestStack->getCurrentRequest()->get('plan');
    if (empty($id)) {
      return new JsonResponse(
        ["message" => "Missing 'plan' query parameter value"],
        400,
        ["Access-Control-Allow-Origin" => "*"]
      );
    }

    try {
      $response = $this->harvester
        ->getHarvestRunInfo($id, $identifier);

      return new JsonResponse(
            json_decode($response),
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
   */
  public function revert() {
    try {

      $id = $this->requestStack->getCurrentRequest()->get('plan');
      if (empty($id)) {
        return new JsonResponse(
          ["message" => "Missing 'plan' query parameter value"],
          400,
          ["Access-Control-Allow-Origin" => "*"]
        );
      }

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
  private function exceptionJsonResponse(\Exception $e, int $code = 400) {
    return $this->jsonResponse(['message' => $e->getMessage()], $code);
  }

  /**
   * Private.
   */
  private function jsonResponse(array $return, int $code = 400) {
    return new JsonResponse(
      (object) $return,
      $code
    );
  }

}
