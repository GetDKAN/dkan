<?php

namespace Drupal\interra_api\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
* An example controller.
*/
class ApiController extends ControllerBase {

  /**
  * Returns a render-able array for a test page.
  */
  public function routes() {
    $build = [
    '#markup' => $this->t('Hello World!'),
    ];

    return $build;
  }
}
