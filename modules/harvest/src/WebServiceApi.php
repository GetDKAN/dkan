<?php

namespace Drupal\harvest;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Harvest API controller.
 *
 * @todo Remove reliance on the request stack.
 * @todo Add cache tags/contexts.
 */
class WebServiceApi implements ContainerInjectionInterface {

  private const DEFAULT_HEADERS = ['Access-Control-Allow-Origin' => '*'];

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
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack'),
      $container->get('dkan.harvest.service')
    );
  }

  /**
   * Constructor.
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
      $rows = $this->harvester->getAllHarvestIds();
      return new JsonResponse(
        $rows,
        200,
        static::DEFAULT_HEADERS
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
      if ($plan = $this->harvester->getHarvestPlanObject($identifier)) {
        return new JsonResponse(
          $plan,
          200,
          static::DEFAULT_HEADERS
        );
      }
      else {
        // There was no plan to retrieve.
        return new JsonResponse(
          ['message' => 'Unable to find plan ' . $identifier],
          404,
          static::DEFAULT_HEADERS
        );
      }
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
        (object) ['identifier' => $identifier],
        200,
        static::DEFAULT_HEADERS
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
    $result = (object) ['identifier' => $identifier];
    $status = 200;
    try {
      if (!$this->harvester->deregisterHarvest($identifier)) {
        // We couldn't find a harvest plan to deregister.
        $status = 404;
        $result->message = 'Unable to find plan ' . $identifier;
      }
      return new JsonResponse(
        $result, $status, static::DEFAULT_HEADERS
      );
    }
    catch (\Exception) {
      // Send a new exception through so that SQL errors and the like will not
      // be given to users.
      return $this->exceptionJsonResponse(
        new \Exception('Unable to deregister harvest plan ' . $identifier)
      );
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
          'message' => 'Invalid payload.',
          'documentation' => '/api/1/harvest',
        ];
        return $this->jsonResponse($return, 400);
      }

      $id = $payload->plan_id;
      $result = $this->harvester
        ->runHarvest($id);

      return new JsonResponse(
        (object) [
          'identifier' => $id,
          'result' => $result,
        ],
        200,
        static::DEFAULT_HEADERS
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
        return $this->missingParameterJsonResponse('plan');
      }

      // Harvester->getRunIdsForHarvest() returns a keyed array, which
      // json_encode() turns into an object. Therefore, we use array_values() to
      // get rid of those keys.
      $response = array_values(
        $this->harvester->getRunIdsForHarvest($id)
      );

      return new JsonResponse(
        $response,
        200,
        static::DEFAULT_HEADERS
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
   *   The harvest run identifier.
   */
  public function infoRun($identifier) {

    $plan_id = $this->requestStack->getCurrentRequest()->get('plan');
    if (empty($plan_id)) {
      return $this->missingParameterJsonResponse('plan');
    }

    try {
      $response = $this->harvester
        ->getHarvestRunInfo($plan_id, $identifier);

      return new JsonResponse(
        json_decode($response),
        200,
        static::DEFAULT_HEADERS
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
      $plan_id = $this->requestStack->getCurrentRequest()->get('plan');
      if (empty($plan_id)) {
        return $this->missingParameterJsonResponse('plan');
      }
      $result = $this->harvester->revertHarvest($plan_id);
      return new JsonResponse(
        (object) [
          'identifier' => $plan_id,
          'result'     => $result,
        ],
        200,
        static::DEFAULT_HEADERS
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
   *
   * @todo Is this really needed?
   */
  private function jsonResponse(array $return, int $code = 400) {
    return new JsonResponse(
      (object) $return,
      $code
    );
  }

  /**
   * Standardized response for a missing parameter.
   *
   * @param string $parameter
   *   The missing parameter.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Response.
   */
  private function missingParameterJsonResponse(string $parameter) {
    return new JsonResponse(
      ['message' => "Missing '" . $parameter . "' query parameter value"],
      400,
      static::DEFAULT_HEADERS
    );
  }

}
