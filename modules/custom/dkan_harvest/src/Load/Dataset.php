<?php

namespace Drupal\dkan_harvest\Load;

use Harvest\ETL\Load\Load;

/**
 * Class.
 */
class Dataset extends Load {

  /**
   * Public.
   */
  public function removeItem($id) {
    $engine = $this->getDatasetEngine();
    $engine->delete($id);
  }

  /**
   * Private.
   */
  protected function saveItem($item) {
    $engine = $this->getDatasetEngine();
    $engine->post(json_encode($item));
  }

  /**
   * Get the engine from the Dataset Controller.
   *
   * @TODO Shouldn't use controller inner workings like this. Should refactor to service.
   *
   * @return \Sae\Sae
   *   Sae object.
   *
   * @codeCoverageIgnore
   */
  protected function getDatasetEngine() {
    /** @var \Drupal\dkan_api\Controller\Dataset $dataset_controller */
    $dataset_controller = \Drupal::service('dkan_metastore.controller');
    return $dataset_controller->getEngine('dataset');
  }

}
