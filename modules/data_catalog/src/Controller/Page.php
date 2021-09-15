<?php

namespace Drupal\data_catalog\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * An example controller.
 */
class Page extends ControllerBase {

  /**
   * Returns a render-able array.
   */
  public function content() {
    return [
      '#theme' => 'page__data_catalog',
    ];
  }

}
