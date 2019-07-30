<?php

namespace Drupal\dkan_harvest\Service;

use Drupal\dkan_harvest\Storage\File;
use Harvest\ETL\Factory as EtlFactory;
use Harvest\Harvester;
use Harvest\ResultInterpreter;
use Harvest\Storage\Storage;
use Drupal\dkan_harvest\Service\Factory as HarvestFactory;
use Drupal\dkan_common\Service\JsonUtil;
use Drupal\Component\Datetime\TimeInterface;

/**
 * Base service class for dkan_harvest.
 */
class Harvest {

  /**
   *
   * @var HarvestFactory
   */
  protected $factory;

  /**
   *
   * @var JsonUtil
   */
  protected $jsonUtil;

  /**
   *
   * @var TimeInterface
   */
  protected $time;


  public function __construct(HarvestFactory $factory, JsonUtil $jsonUtil, TimeInterface $time) {
    $this->factory = $factory;
    $this->jsonUtil = $jsonUtil;
    $this->time = $time;
  }

  /**
   * Get all available harvests.
   * @return array
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
   * @param \stdClass $plan usually an \stdClass representation.
   * @return string identifier.
   * @throws \Exception exceptions may be thrown if validation fails.
   */
  public function registerHarvest(\stdClass $plan) {

    $this->validateHarvestPlan($plan);
    return $this->factory
        ->getPlanStorage()
        ->store(json_encode($plan), $plan->identifier);
  }

  /**
   * Deregister harvest.
   * 
   * @param string $id
   * @return bool
   */
  public function deregisterHarvest(string $id) {
    $this->revertHarvest($id);
    return $this->factory
        ->getPlanStorage()
        ->remove($id);
  }

  public function revertHarvest($id) {
    return $this->factory
        ->getHarvester($id)
        ->revert();
  }

  public function runHarvest($id) {
    $result = $this->factory
      ->getHarvester($id)
      ->harvest();
    // store result of the run.
    $this->factory
      ->getStorage($id, "run")
      ->store(json_encode($result), $this->time->getCurrentTime());

    return $result;
  }

  /**
   *      *
   * @param mixed $id
   * @param mixed $runId
   * @return mixed FALSE if no matching runID is found.
   */
  public function getHarvestRunInfo($id, $runId) {
    $allRuns = $this->getAllHarvestRunInfo($id);
    return isset($allRuns[$runId]) ? $allRuns[$runId] : FALSE;
  }

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
   * @todo is calling a static class.
   * @param \stdClass $plan
   * @return bool Throws exceptions instead of false it seems.
   */
  public function validateHarvestPlan(\stdClass $plan) {
    return EtlFactory::validateHarvestPlan($plan);
  }

}
