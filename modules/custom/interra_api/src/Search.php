<?php

namespace Drupal\interra_api;

/**
 *
 */
class Search {

  /**
   *
   */
  public function formatDocs($docs) {
    $index = [];
    foreach ($docs as $id => $doc) {
      $index[] = $this->formatSearchDoc($doc);
    }
    return $index;
  }

  /**
   *
   */
  public function formatSearchDoc($value) {
    $formatted      = new \stdClass();
    $formatted->doc = $value;
    $formatted->ref = "";
    return $formatted;
  }

  /**
   *
   */
  public function index() {
    $datasets = [];

    /** @var Service\DatasetModifier $dataset_modifier */
    $dataset_modifier = \Drupal::service('interra_api.service.dataset_modifier');

    foreach ($this->getDatasets() as $dataset) {
      $datasets[] = $dataset_modifier->modifyDataset($dataset);
    }

    return $this->formatDocs($datasets);
  }

  /**
   * Get datasets.
   *
   * @TODO Shouldn't use controller inner workings like this. Should refactor to service.
   *
   * @return array Array of dataset objects
   */
  protected function getDatasets() {
    /** @var \Drupal\dkan_api\Controller\Dataset $dataset_controller */
    $dataset_controller = \Drupal::service('dkan_api.controller.dataset');

    // Engine returns array of json strings.
    return array_map(
            function ($item) {
              return json_decode($item);
            },
            $dataset_controller->getEngine()
              ->get()
    );
  }

}
