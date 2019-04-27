<?php

namespace Drupal\interra_api;

use Drupal\dkan_api\Controller\Dataset;
use Drupal\interra_api\Controller\ApiController;

class Search {

  public function formatDocs($docs) {
    $index = array();
    foreach($docs as $id => $doc) {
      $index[] = $this->formatSearchDoc($doc);
    }
    return $index;
  }
  public function formatSearchDoc($value) {
    $formatted = new \stdClass();
    $formatted->doc = $value;
    $formatted->ref = "";
    return $formatted;
  }

  public function index() {
    $datasets = [];

    $api_engine = $this->getDatasetEngine();
    $array_of_json_strings = $api_engine->get();
    $json_string = "[" . implode(",", $array_of_json_strings) . "]";
    $array = json_decode($json_string);

    foreach ($array as $dataset) {
      $datasets[] = ApiController::modifyDataset($dataset);
    }

    return $this->formatDocs($datasets);
  }

   /**
   * Get the engine from the Datset Controller.
   *
   * @TODO Shouldn't use controller inner workings like this. Should refactor to service.
   * @return \Sae\Sae
   */
  protected function getDatasetEngine() {
    /** @var \Drupal\dkan_api\Controller\Dataset $dataset_controller */
      $dataset_controller = \Drupal::service('dkan_api.controller.dataset');
      return $dataset_controller->getEngine();
  }

}
