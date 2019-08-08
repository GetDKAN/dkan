<?php

namespace Drupal\dkan_harvest\Service;

use Harvest\ETL\Factory as EtlFactory;
use Drupal\dkan_harvest\Service\Factory as HarvestFactory;
use Drupal\dkan_common\Service\JsonUtil;
use Drupal\Component\Datetime\TimeInterface;

/**
 * Base service class for dkan_harvest.
 */
class Harvest {

  /**
   * Factory.
   *
   * @var \Drupal\dkan_harvest\Service\Factory
   */
  protected $factory;

  /**
   * JsonUtil.
   *
   * @var \Drupal\dkan_common\Service\JsonUtil
   */
  protected $jsonUtil;

  /**
   * Time.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * Public.
   */
  public function __construct(HarvestFactory $factory, JsonUtil $jsonUtil, TimeInterface $time) {
    $this->factory = $factory;
    $this->jsonUtil = $jsonUtil;
    $this->time = $time;
  }

  /**
   * Get all available harvests.
   *
   * @return array
   *   All ids.
   */
  public function getAllHarvestIds() {

    return array_keys(
      $this->factory
        ->getPlanStorage()
        ->retrieveAll()
    );
  }

  /**
   * Register a new harvest plan.
   *
   * @param object $plan
   *   usually an \stdClass representation.
   *
   * @return string
   *   Identifier.
   *
   * @throws \Exception
   *   Exceptions may be thrown if validation fails.
   */
  public function registerHarvest($plan) {

    $this->validateHarvestPlan($plan);
    return $this->factory
      ->getPlanStorage()
      ->store(json_encode($plan), $plan->identifier);
  }

  /**
   * Deregister harvest.
   *
   * @param string $id
   *   Id.
   *
   * @return bool
   *   Boolean.
   */
  public function deregisterHarvest(string $id) {
    $this->revertHarvest($id);
    return $this->factory
      ->getPlanStorage()
      ->remove($id);
  }

  /**
   * Public.
   */
  public function revertHarvest($id) {
    return $this->factory
      ->getHarvester($id)
      ->revert();
  }

  /**
   * Public.
   */
  public function runHarvest($id) {
    $result = $this->factory
      ->getHarvester($id)
      ->harvest();
    // Store result of the run.
    $this->factory
      ->getStorage($id, "run")
      ->store(json_encode($result), $this->time->getCurrentTime());

    return $result;
  }

  /**
   * Get Harvest Run Info.
   *
   * @return mixed
   *   FALSE if no matching runID is found.
   */
  public function getHarvestRunInfo($id, $runId) {
    $allRuns = $this->getAllHarvestRunInfo($id);
    return isset($allRuns[$runId]) ? $allRuns[$runId] : FALSE;
  }

  /**
   * Public.
   */
  public function getAllHarvestRunInfo($id) {
    return $this->jsonUtil
      ->decodeArrayOfJson(
          $this->factory
            ->getStorage($id, 'run')
            ->retrieveAll()
    );
  }

  /**
   * Proxy to Etl Factory to validate harvest plan.
   *
   * @param object $plan
   *   Plan.
   *
   * @return bool
   *   Throws exceptions instead of false it seems.
   */
  public function validateHarvestPlan($plan) {
    return EtlFactory::validateHarvestPlan($plan);
  }

}
