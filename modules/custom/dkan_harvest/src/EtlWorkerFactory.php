<?php

namespace Drupal\dkan_harvest;


class EtlWorkerFactory {

  private $harvestPlan;

  public function __construct($harvest_plan) {
    $this->harvestPlan = $harvest_plan;
  }

  public function get($type) {
    if ($type == "extract") {
      $name = $this->harvestPlan->source->type;
      return  $this->getOne($type, $name);
    }
    elseif ($type == "load") {
      $name = $this->harvestPlan->load->type;
      return  $this->getOne($type, $name);
    }
    elseif($type == "transforms") {
      $transforms = [];

      foreach ($this->harvestPlan->transforms as $info) {
        $config = NULL;

        if (is_object($info)) {
          $info = (array) $info;
          $name = array_keys($info)[0];
        }
        else {
          $name = $info;
        }
        $transforms[] = $this->getOne('transform', $name, $this->harvestPlan);
      }

      return $transforms;
    }
  }

  private function getOne($type, $name, $config = NULL) {
    $type = ucfirst($type);
    $class = "Drupal\\dkan_harvest\\{$type}\\" . $name;
    if (!$config) {
      $config = $this->harvestPlan;
    }
    return new $class($config);
  }

}