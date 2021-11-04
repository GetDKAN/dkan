<?php

namespace Drupal\dkan_js_frontend\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * The Page controller.
 */
class Page extends ControllerBase {

  /**
   * Returns a render-able array.
   */
  public function content() {
    return [
      '#theme' => 'page__dkan_js_frontend',
    ];
  }

}
