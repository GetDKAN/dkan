<?php

namespace Drupal\dkan_harvest\Load;

use Harvest\ETL\Load\Load;

/**
 *
 */
class Dataset extends Load {

  public function removeItem($id) {
    $engine = $this->getDatasetEngine();
    $engine->delete($id);
  }

  /**
   *
   */
  protected function saveItem($item) {
    $engine = $this->getDatasetEngine();
    $engine->post(json_encode($item));
  }

  /**
   * Get the engine from the Datset Controller.
   *
   * @TODO Shouldn't use controller inner workings like this. Should refactor to service.
   *
   * @return \Sae\Sae
   *
   * @codeCoverageIgnore
   */
  protected function getDatasetEngine() {
    /** @var \Drupal\dkan_api\Controller\Dataset $dataset_controller */
    $dataset_controller = \Drupal::service('dkan_api.controller.dataset');
    return $dataset_controller->getEngine();
  }

}
