<?php

namespace Drupal\dkan_harvest\Load;

use Harvest\Load\Load;

class Dataset extends Load {

  protected function saveItem($item) {
    $dataset_controller = new \Drupal\dkan_api\Controller\Dataset();
    /* @var $enginer \Sae\Sae */
    $engine = $dataset_controller->getEngine();
    $engine->post(json_encode($item));
  }

}