<?php

namespace Drupal\dkan_harvest;

use Drupal\dkan_harvest\Log\MakeItLog;
use Drupal\dkan_harvest\Storage\Run;

class Harvester {
  use MakeItLog;

  private $harvestPlan;

  private $factory;

  public function __construct($harvest_plan) {
    $this->harvestPlan = $harvest_plan;

    $this->factory = new EtlWorkerFactory($harvest_plan);

    return $this->validateHarvestPlan();
  }

  public function harvest() {
    $items = $this->extract();
    $items = $this->transform($items);
    $results = $this->load($items);

    $run_store = new Run();
    $run_store->create($this->harvestPlan->sourceId, $results);

    return $results;
  }

  private function extract() {
    $extract = $this->factory->get('extract');

    if ($this->logger) {
      $extract->setLogger($this->logger);
    }

    $items = $extract->run();
    return $items;
  }

  private function transform($items) {
    $transforms = $this->factory->get("transforms");
    foreach ($transforms as $transform) {
      if ($this->logger) {
        $transform->setLogger($this->logger);
      }
      $transform->run($items);
    }
    return $items;
  }

  private function load($items) {
    $load = $this->factory->get('load');
    if ($this->logger) {
      $load->setLogger($this->logger);
    }
    return $load->run($items);
  }

  private function validateHarvestPlan() {
    $harvest = $this->harvestPlan;
    if (isset($harvest->sourceId) &&
      isset($harvest->transforms) &&
      isset($harvest->load) &&
      isset($harvest->source) &&
      isset($harvest->source->type) &&
      isset($harvest->source->uri)) {
      return TRUE;
    }
    else {
      throw new \Exception('Harvester settings missing property.');
    }
  }

}
