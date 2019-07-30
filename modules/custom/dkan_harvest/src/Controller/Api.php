<?php

namespace Drupal\dkan_harvest\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use \Drupal\dkan_common\Util\RequestTrait;
use Drupal\dkan_harvest\Service\Harvest as HarvestService;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\dkan_common\Service\Factory as DkanFactory;

/**
 * Class Api.
 *
 * @package Drupal\dkan_harvest\Controller
 * @codeCoverageIgnore
 */
class Api extends ControllerBase {

  use RequestTrait;

  /**
   * Drupal service container.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface
   */
  protected $container;

  /**
   *
   * @var HarvestService
   */
  protected $harvestService;

  /**
   *
   * @var LoggerChannelInterface
   */
  protected $logger;

  /**
   *
   * @var DkanFactory
   */
  protected $dkanFactory;

  /**
   * Api constructor.
   */
  public function __construct(ContainerInterface $container) {
    $this->container      = $container;
    $this->harvestService = $this->container->get('dkan_harvest.service');
    $this->logger         = $this->container->get('dkan_harvest.logger_channel');
    $this->dkanFactory    = $this->container->get('dkan.factory');
  }

  /**
   * {@inheritdoc}
   *
   * @codeCoverageIgnore
   */
  public static function create(ContainerInterface $container) {
    return new static($container);
  }

  /**
   * List harvest ids
   */
  public function index() {

    try {

      $rows = $this->harvestService
        ->getAllHarvestIds();

      return $this->dkanFactory
          ->newJsonResponse(
            $rows,
            200,
            ["Access-Control-Allow-Origin" => "*"]
      );
    } catch (\Exception $e) {
      return $this->dkanFactory
          ->newJsonResponse(
            (object) [
              'message' => $e->getMessage(),
            ],
            500
      );
    }
  }

  /**
   * Register a new harvest.
   */
  public function register() {
    try {

      // post data normally.
      $harvest_plan = $this->getCurrentRequestContent();
      $plan         = json_decode($harvest_plan);
      $identifier   = $this->harvestService
        ->registerHarvest($plan);

      return $this->dkanFactory
          ->newJsonResponse(
            (object) [
              "endpoint"   => $this->getCurrentRequestUri(),
              "identifier" => $identifier
            ],
            200,
            [
              "Access-Control-Allow-Origin" => "*"
            ]
      );
    } catch (\Exception $e) {
      return $this->dkanFactory
          ->newJsonResponse(
            (object) [
              'message' => $e->getMessage(),
            ],
            500
      );
    }
  }

  /**
   * Deregister a harvest.
   */
  public function deregister($id) {

    try {

      $this->harvestService
        ->deregisterHarvest($id);

      return $this->dkanFactory
          ->newJsonResponse(
            (object) [
              "endpoint"   => $this->getCurrentRequestUri(),
              "identifier" => $id
            ],
            200,
            ["Access-Control-Allow-Origin" => "*"]
      );
    } catch (\Exception $e) {
      return $this->dkanFactory
          ->newJsonResponse(
            (object) [
              'message' => $e->getMessage(),
            ],
            500
      );
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

      $result = $this->harvestService
        ->runHarvest($id);

      return $this->dkanFactory
          ->newJsonResponse(
            (object) [
              "endpoint"   => $this->getCurrentRequestUri(),
              "identifier" => $id,
              "result"     => $result,
            ],
            200,
            ["Access-Control-Allow-Origin" => "*"]
      );
    } catch (\Exception $e) {
      return $this->dkanFactory
          ->newJsonResponse(
            (object) [
              'message' => $e->getMessage(),
            ],
            500
      );
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


        $response = array_keys($this->harvestService
          ->getAllHarvestRunInfo($id));


      return $this->dkanFactory
          ->newJsonResponse(
            $response,
            200,
            ["Access-Control-Allow-Origin" => "*"]
      );
    } catch (\Exception $e) {
      return $this->dkanFactory
          ->newJsonResponse(
            (object) [
              'message' => $e->getMessage(),
            ],
            500
      );
    }
  }

  /**
   * Gives information about a single previous harvest run.
   *
   * @param string $id
   *   The harvest id.
   * @param string $run_id
   *   The run's id
   */
  public function infoRun($id, $run_id) {

    try {

        $response = $this->harvestService
          ->getHarvestRunInfo($id, $run_id);

      return $this->dkanFactory
          ->newJsonResponse(
            $response,
            200,
            ["Access-Control-Allow-Origin" => "*"]
      );
    } catch (\Exception $e) {
      return $this->dkanFactory
          ->newJsonResponse(
            (object) [
              'message' => $e->getMessage(),
            ],
            500
      );
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

      $result = $this->harvestService
        ->revertHarvest($id);

      return $this->dkanFactory
          ->newJsonResponse(
            (object) [
              "endpoint"   => $this->getCurrentRequestUri(),
              "identifier" => $id,
              'result'     => $result
            ],
            200,
            [
              "Access-Control-Allow-Origin" => "*"
            ]
      );
    } catch (\Exception $e) {
      return $this->dkanFactory
          ->newJsonResponse(
            (object) [
              'message' => $e->getMessage(),
            ],
            500
      );
    }
  }

}
